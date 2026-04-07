# HE BACKS UP

> WordPress 사이트를 통째로 백업하고, 언제든 복구하는 플러그인

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue?logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange)](https://github.com/)

---

## 소개

**HE BACKS UP**은 WordPress 사이트의 **파일(wp-content)과 데이터베이스**를 한 번에 백업하고 복구할 수 있는 무료 플러그인입니다.

복잡한 설정 없이 WordPress 관리자 화면에서 바로 백업할 수 있으며, **Google Drive**에 자동 저장하거나 **자동 스케줄 백업**으로 사이트를 안전하게 지킬 수 있습니다.

---

## 주요 기능

| 기능 | 설명 |
|------|------|
| **즉시 백업** | 버튼 한 번으로 파일 + DB 전체 백업 |
| **자동 스케줄 백업** | 매일 / 매주 / 매월 자동 실행 |
| **로컬 서버 저장** | 서버 내부에 ZIP 파일로 백업 보관 |
| **Google Drive 저장** | OAuth 연동으로 Google Drive에 자동 업로드 |
| **복구** | 백업 파일로 사이트 전체 복구 |
| **보존 정책** | 오래된 백업 자동 삭제로 용량 절약 |
| **백업 로그** | 백업/복구 기록 확인 |

---

## 스크린샷

> 대시보드, 설정 화면 등 스크린샷은 추후 추가될 예정입니다.

---

## 설치 방법

### 방법 1 — ZIP 파일 직접 업로드 (권장)

1. 이 저장소의 **`plugin-dev`** 폴더를 ZIP으로 압축하거나, [Releases](../../releases) 페이지에서 최신 버전을 다운로드합니다.
2. WordPress 관리자 → **플러그인 → 새로 추가 → 플러그인 업로드**
3. ZIP 파일을 선택하고 **지금 설치** 클릭
4. 설치 완료 후 **플러그인 활성화**

### 방법 2 — FTP / 파일 관리자

1. `plugin-dev` 폴더 전체를 서버의 `/wp-content/plugins/he-backs-up/` 경로에 업로드
2. WordPress 관리자 → **플러그인** → **HE BACKS UP** 활성화

---

## 사용 방법

### 즉시 백업

1. WordPress 관리자 → **HE BACKS UP** 메뉴 이동
2. **지금 백업 시작** 버튼 클릭
3. 백업 완료 후 목록에서 파일 확인

### Google Drive 연동

1. **HE BACKS UP → Google Drive 설정** 이동
2. **Google 계정 연결** 버튼 클릭 후 권한 허용
3. 설정 → 저장소 설정에서 **Google Drive 저장** 활성화

### 자동 스케줄 백업

1. **HE BACKS UP → 설정** 이동
2. **자동 백업** 활성화 체크
3. 백업 주기 선택 (매일 / 매주 / 매월)
4. **설정 저장**

> **팁:** 더 안정적인 자동 백업을 위해 서버 Cron 설정을 권장합니다.
> ```
> */5 * * * * curl -s https://your-site.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
> ```

---

## 시스템 요구사항

- **WordPress** 5.0 이상
- **PHP** 7.4 이상
- **MySQL** 5.6 이상
- PHP 확장: `ZipArchive`, `PDO` 또는 `mysqli`

---

## 저장소 구조

```
He-Backs-up/
├── plugin-dev/          # 플러그인 본체 (WordPress에 설치할 파일)
│   ├── he-backs-up.php  # 플러그인 메인 파일
│   ├── includes/        # 핵심 클래스 (백업, 복구, DB, Google Drive 등)
│   ├── admin/           # 관리자 페이지 UI
│   ├── assets/          # CSS / JS
│   └── oauth-relay/     # Google OAuth 릴레이 서버 파일
└── web-dev/             # 공식 소개 웹사이트 소스
```

---

## 라이선스

[GPL v2.0](https://www.gnu.org/licenses/gpl-2.0) 라이선스로 배포됩니다. 자유롭게 사용, 수정, 배포할 수 있습니다.

---

## 기여 / 문의

버그 제보나 기능 제안은 [Issues](../../issues) 탭에 남겨주세요.
