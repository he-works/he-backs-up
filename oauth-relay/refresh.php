<?php
/**
 * He Backs Up — OAuth 토큰 갱신 릴레이
 *
 * 설치 방법:
 *   callback.php 와 같은 디렉토리에 업로드합니다.
 *   예: https://hebacksup.com/oauth/refresh.php
 *
 * 플러그인이 refresh_token → access_token 교환을 요청합니다.
 * Client Secret이 이 파일에만 존재하므로 플러그인 코드에는 노출되지 않습니다.
 */

define( 'RELAY_CLIENT_ID',     'YOUR_CLIENT_ID_HERE' );     // callback.php와 동일한 값
define( 'RELAY_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE' ); // callback.php와 동일한 값

// HTTPS 강제
if (
    ( ! isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] !== 'on' ) &&
    ( ! isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https' )
) {
    json_error( 'HTTPS 연결이 필요합니다.' );
}

// POST 요청만 허용
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    json_error( 'POST 요청만 허용됩니다.' );
}

$refresh_token = $_POST['refresh_token'] ?? '';

if ( empty( $refresh_token ) ) {
    json_error( 'refresh_token이 없습니다.' );
}

// Google Token API 호출
$context = stream_context_create( [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query( [
            'client_id'     => RELAY_CLIENT_ID,
            'client_secret' => RELAY_CLIENT_SECRET,
            'refresh_token' => $refresh_token,
            'grant_type'    => 'refresh_token',
        ] ),
        'timeout' => 15,
    ],
    'ssl' => [
        'verify_peer'      => true,
        'verify_peer_name' => true,
    ],
] );

$result = @file_get_contents( 'https://oauth2.googleapis.com/token', false, $context );

if ( $result === false ) {
    json_error( 'Google Token API 호출 실패.' );
}

$data = json_decode( $result, true );

if ( empty( $data['access_token'] ) ) {
    json_error( 'access_token을 받지 못했습니다: ' . ( $data['error_description'] ?? '알 수 없는 오류' ) );
}

// 성공 응답
header( 'Content-Type: application/json' );
echo json_encode( [
    'access_token' => $data['access_token'],
    'expires_in'   => $data['expires_in'] ?? 3600,
] );
exit;

function json_error( $message ) {
    http_response_code( 400 );
    header( 'Content-Type: application/json' );
    echo json_encode( [ 'error' => $message ] );
    exit;
}
