# 🔐 Symfony Biometric Two-Factor Authentication (2FA)

This project demonstrates how to implement biometric authentication (using WebAuthn) as a second layer of security in a Symfony application. It also includes traditional email-based TOTP verification, giving users the ability to enable/disable either method from their settings.

## 📦 Features

- Biometric authentication using WebAuthn (FIDO2)
- Email-based One-Time Password (OTP) authentication
- Toggle 2FA options from user settings
- Event-driven 2FA redirection
- Secure session handling for 2FA
- Bootstrap-based UI

## 🚀 Requirements

- PHP 8.4+
- Symfony 7.2+
- Composer
- MySQL or any supported database
- A modern browser with WebAuthn support (e.g., Chrome, Edge, Safari)

## 🛠 Installation

```bash
git clone https://github.com/niravpateljoin/biometric-auth.git
cd biometric-auth
composer install
```

## ⚙️ Setup

1. Create and configure your `.env.local` file:

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/biometric_2fa"
OTP_MASTER_KEY="your-secret-key"
```

2. Run migrations and load fixtures:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

3. Start the Symfony server:

```bash
symfony serve --listen-ip=0.0.0.0
```

Visit `http://localhost:8000`

## 🧪 Demo Credentials

```bash
Email: superadmin@system.com
Password: Admin#@123
```


## 🎨 Frontend

- Bootstrap 5
- Font Awesome icons
- SweetAlert for user-friendly messages

## 📄 License

MIT License

---

If you find this useful, feel free to ⭐ the repo or submit improvements!
