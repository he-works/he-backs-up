# Server

## **오라클 서버**

[https://cloud.oracle.com/](https://cloud.oracle.com/)

**계정** : [jushan1980@gmail.com](mailto:jushan1980@gmail.com)
비밀번호와 오라클 인증앱을 통해 로그인.

| 테넌시 | heworks |  |
| --- | --- | --- |
| **Compartment** | prod-web |  |
| 구성 | VM.Standard.A1.Flex |  |
| OCPU 개수 | 2 |  |
| 네트워크 대역폭(Gbps) | 2 |  |
| 메모리(GB) | 8 |  |
| **Instance** | prod-web-01 |  |
| **Public IP** | 158.179.170.179 |  |
| **OS** | Ubuntu 22.04 |  |

```bash
**/* SSH접속 */**
ssh -i ~/.ssh/oci-keys/oci-prod-deploy ubuntu@158.179.170.179
****ssh -i ~/.ssh/oci-keys/oci-prod-web ubuntu@158.179.170.179

# 아래 명령어로 접속 가능
ssh oracle
ssh -i ~/.ssh/oci-keys/oci-prod-web ubuntu@158.179.170.179

# vi 파일 편집
Host oracle 
 HostName 158.179.170.179
 User ubuntu 
 IdentityFile ~/.ssh/oci-keys/oci-prod-web
 Port 22

# 서버의 SSH 키
~/.ssh/id_ed25519

# Git Pull
cd /home/ubuntu/oracle-server
git pull

# SSH 파일 업로드
scp -O "$heblogs/functions.php" oracle:/opt/sites/heworks-blog/wp-content/themes/HeBlogs/functions.php
```

## 서버 디렉토리 구조

```bash
┌─────────────────────────────────────────────────────────────────────────────┐
│                          Nginx (호스트, 80/443)                               │
│                          Let's Encrypt SSL                                  │
├───────────────┬───────────────┬──────────────┬──────────────┬───────────────┤
│  he-works.co  │ temp.peterosea│ blog.he-works│ openclaw.he  │ billr.he-works│
│  www.he-works │    .com       │    .co       │  -works.co   │    .co        │
│  .co  ★ NEW   │               │              │              │               │
├───────────────┼───────────────┼──────────────┼──────────────┼───────────────┤
│  정적 HTML     │   WordPress   │  WordPress   │  OpenClaw    │  PHP:8.2      │
│  Nginx 직접    │  + MariaDB    │  + MariaDB   │              │  + Apache     │
│  서빙          │  (Docker)     │  (Docker)    │  (Docker)    │  (Docker)     │
│               │    :8081      │    :8082     │    :8090     │    :8083      │
└───────────────┴───────────────┴──────────────┴──────────────┴───────────────┘

```

### Ubuntu

```bash
/home/ubuntu/oracle-server/          ← Git 레포 (설정 원본 저장소)
├── README.md
├── Makefile
├── cloudflare-pages.toml
├── .github/workflows/
│   ├── deploy-wp.yml                 ← WordPress 배포 CI/CD
│   ├── openclaw-maint.yml            ← OpenClaw 유지보수
│   └── cloudflare-pages.yml          ← 정적 사이트 배포
├── scripts/                          ← 로컬 헬퍼 스크립트
│   ├── bootstrap.sh
│   ├── deploy.sh
│   ├── db-sync.sh
│   └── wp-sync-uploads.sh
└── oci-os/                           ← 서버 배포용 설정
    ├── config/nginx/                 ← Nginx 설정 원본
    │   ├── peterosea.conf             → temp.peterosea.com  :8081
    │   ├── heworks-blog.conf          → blog.he-works.co    :8082
    │   ├── openclaw.conf              → openclaw.he-works.co :8090
    │   └── billr.conf                 → billr.he-works.co   :8083
    ├── docker/                       ← Docker Compose 원본
    │   ├── peterosea-wp/
    │   │   ├── docker-compose.yml
    │   │   └── .env.example
    │   ├── he-blogs/
    │   │   ├── docker-compose.yml
    │   │   └── .env.example
    │   ├── openclaw/
    │   │   ├── docker-compose.yml
    │   │   └── .env.example
    │   └── billr/
    │       └── docker-compose.yml
    └── scripts/
        └── backup-db.sh
```

### Opt

```bash
/opt/                                 ← 서버 실행 환경 (실제 운영)
├── stacks/                           ← Docker Compose 실행 파일
│   ├── peterosea-wp/
│   │   ├── docker-compose.yml
│   │   ├── .env
│   │   └── .env.example
│   ├── he-blogs/
│   │   ├── docker-compose.yml
│   │   ├── .env
│   │   └── .env.example
│   ├── openclaw/
│   │   ├── docker-compose.yml
│   │   ├── .env
│   │   └── .env.example
│   └── billr/
│       └── docker-compose.yml
│
├── sites/                            ← 사이트 콘텐츠 / 소스코드
│   ├── heworks-home/                  ★ NEW — Nginx 직접 서빙 (Docker 없음)
│   │   └── index.html
│   ├── peterosea-wp/
│   │   └── wp-content/
│   │       ├── plugins/
│   │       ├── themes/
│   │       ├── uploads/
│   │       └── languages/
│   ├── he-blogs/
│   │   └── wp-content/
│   │       ├── plugins/
│   │       ├── themes/ (HeBlogs 커스텀 테마)
│   │       ├── uploads/
│   │       └── languages/
│   └── billr/                         (git: he-works/quotation)
│       ├── index.php
│       ├── api.php
│       ├── editor.php
│       ├── list.php
│       ├── config.php
│       ├── .htaccess
│       ├── css/
│       ├── js/
│       ├── img/
│       ├── inc/ (header.php, footer.php)
│       └── data/ [www-data 소유, PHP 쓰기]
│           ├── quotes.json
│           ├── clients.json
│           └── login_attempts.json
│
└── apps/                             ← 백엔드 애플리케이션
    └── quant_bot/
        ├── main.py
        ├── layer1~5_*.py
        ├── .env
        └── logs/

```

‣ 

[계정정보](https://www.notion.so/326cbce4dc07805ba61ee0e834ac6d12?pvs=21)

[peterosea.com](https://www.notion.so/peterosea-com-30bcbce4dc0780c4a24adc8df8a045ae?pvs=21)

[He.Works.Co](https://www.notion.so/He-Works-Co-30bcbce4dc078015bc19ca43e53e3681?pvs=21)