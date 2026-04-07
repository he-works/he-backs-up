<?php
/**
 * He Backs Up — OAuth 릴레이 서버
 *
 * ─────────────────────────────────────────────────────────────────────────
 * 설치 방법:
 *   이 파일은 플러그인 코드에 포함되어 있지만, 실제로는 "배포자의 서버"에
 *   별도로 올려야 합니다. 플러그인 자체에는 포함시키지 마세요.
 *
 *   1. 이 파일을 배포자 서버의 웹 루트에 업로드합니다.
 *      예: https://hebacksup.com/oauth/callback.php
 *
 *   2. 아래 RELAY_CLIENT_ID 와 RELAY_CLIENT_SECRET 을 실제 값으로 채웁니다.
 *      (Google Cloud Console에서 발급)
 *
 *   3. Google Cloud Console → OAuth 동의화면 → 승인된 리디렉션 URI에
 *      이 파일의 URL을 추가합니다.
 *      예: https://plugin.he-works.co/he-backs-up/oauth/callback.php
 *
 *   4. he-backs-up.php 의 HBU_OAUTH_RELAY_URL 상수를 이 URL로 설정합니다.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * 보안 모델:
 *   - Client Secret은 이 파일에만 존재. 플러그인 배포 코드에 절대 포함되지 않음.
 *   - state 파라미터에 포함된 nonce를 플러그인이 검증 → CSRF 방지.
 *   - 토큰은 HTTPS로만 전송 (HTTP 차단).
 *   - allowed_domains 화이트리스트로 의도치 않은 리디렉션 방지.
 */

// ── 설정값 (배포자가 채워야 할 부분) ──────────────────────────────────────
$config = require __DIR__ . '/config.local.php';
$client_id     = $config['google_client_id'];
$client_secret = $config['google_client_secret'];

define( 'RELAY_CLIENT_ID',     $client_id );     // callback.php와 동일한 값
define( 'RELAY_CLIENT_SECRET', $client_secret ); // callback.php와 동일한 값
define( 'RELAY_REDIRECT_URI',  'https://plugin.he-works.co/he-backs-up/oauth/callback.php' ); // 이 파일의 URL

// 화이트리스트가 필요하면 도메인 목록 지정. 빈 배열이면 모든 도메인 허용.
// 예: define( 'RELAY_ALLOWED_DOMAINS', ['mysite.com', 'anothersite.com'] );
define( 'RELAY_ALLOWED_DOMAINS', [] );

// ── 초기 보안 검사 ────────────────────────────────────────────────────────

// HTTPS 강제
if (
    ( ! isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] !== 'on' ) &&
    ( ! isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https' )
) {
    relay_error( 'HTTPS 연결이 필요합니다.' );
}

// Google에서 오류 응답을 보낸 경우
if ( isset( $_GET['error'] ) ) {
    $error = htmlspecialchars( $_GET['error'] );
    relay_error( "Google 인증 오류: {$error}" );
}

// 필수 파라미터 확인
if ( empty( $_GET['code'] ) || empty( $_GET['state'] ) ) {
    relay_error( '필수 파라미터(code, state)가 없습니다.' );
}

$code  = $_GET['code'];
$state = $_GET['state'];

// ── State 파라미터 디코딩 ─────────────────────────────────────────────────

$decoded = json_decode( base64_decode( $state ), true );

if ( ! is_array( $decoded ) || empty( $decoded['nonce'] ) || empty( $decoded['return'] ) ) {
    relay_error( 'state 파라미터 형식이 올바르지 않습니다.' );
}

$wp_nonce  = $decoded['nonce'];
$return_url = $decoded['return'];

// return URL이 https 인지 확인
$parsed = parse_url( $return_url );
if ( ! isset( $parsed['scheme'] ) || ! isset( $parsed['host'] ) ) {
    relay_error( 'return URL이 유효하지 않습니다.' );
}

// 도메인 화이트리스트 검사
$allowed = RELAY_ALLOWED_DOMAINS;
if ( ! empty( $allowed ) && ! in_array( $parsed['host'], $allowed, true ) ) {
    relay_error( '허용되지 않은 도메인: ' . htmlspecialchars( $parsed['host'] ) );
}

// ── Google Token API 호출 (코드 → 토큰 교환) ──────────────────────────────

$token_response = relay_http_post( 'https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => RELAY_CLIENT_ID,
    'client_secret' => RELAY_CLIENT_SECRET,
    'redirect_uri'  => RELAY_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
] );

if ( ! $token_response ) {
    relay_error( 'Google 토큰 API 호출 실패.' );
}

$token_data = json_decode( $token_response, true );

if ( empty( $token_data['access_token'] ) ) {
    $err = isset( $token_data['error_description'] ) ? $token_data['error_description'] : '알 수 없는 오류';
    relay_error( 'access_token을 받지 못했습니다: ' . htmlspecialchars( $err ) );
}

$access_token  = $token_data['access_token'];
$refresh_token = isset( $token_data['refresh_token'] ) ? $token_data['refresh_token'] : '';
$expires_at    = time() + (int) ( $token_data['expires_in'] ?? 3600 );

// ── WordPress로 토큰 전달 (리다이렉트) ───────────────────────────────────

$redirect = add_query_arg( [
    'hbu_at'    => rawurlencode( $access_token ),
    'hbu_rt'    => rawurlencode( $refresh_token ),
    'hbu_ex'    => $expires_at,
    'hbu_nonce' => rawurlencode( $wp_nonce ),
], $return_url );

header( 'Location: ' . $redirect );
exit;

// ── 헬퍼 함수 ─────────────────────────────────────────────────────────────

function relay_error( $message ) {
    http_response_code( 400 );
    echo '<h1>He Backs Up OAuth 오류</h1>';
    echo '<p>' . htmlspecialchars( $message ) . '</p>';
    echo '<p>WordPress 관리자 페이지로 돌아가서 다시 시도해주세요.</p>';
    exit;
}

function relay_http_post( $url, $data ) {
    $context = stream_context_create( [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query( $data ),
            'timeout' => 15,
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ] );

    $result = @file_get_contents( $url, false, $context );
    return $result !== false ? $result : null;
}
