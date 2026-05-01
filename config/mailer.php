<?php
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_email(array $env, string $to, string $subject, string $html): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $env['MAIL_HOST']     ?? '';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['MAIL_USERNAME'] ?? '';
        $mail->Password   = $env['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($env['MAIL_PORT'] ?? 587);

        $mail->setFrom(
            $env['MAIL_FROM']      ?? '',
            $env['MAIL_FROM_NAME'] ?? 'FilmVault'
        );
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $html));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

function email_template(string $heading, string $body_html, string $btn_url, string $btn_label): string {
    return <<<HTML
    <!DOCTYPE html>
    <html>
    <body style="margin:0;padding:2rem;background:#0d1117;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif">
      <div style="max-width:480px;margin:0 auto;background:#161b22;border-radius:8px;padding:2rem;border:1px solid #30363d">
        <p style="font-size:1.4rem;font-weight:800;color:#e6edf3;margin:0 0 1.5rem">
          Film<span style="background:#f97316;color:#000;border-radius:4px;padding:1px 7px">Vault</span>
        </p>
        <h2 style="color:#e6edf3;font-size:1.1rem;margin:0 0 1rem">{$heading}</h2>
        <div style="color:#8b949e;font-size:0.95rem;line-height:1.6;margin-bottom:1.5rem">{$body_html}</div>
        <a href="{$btn_url}"
           style="display:inline-block;background:#f97316;color:#000;font-weight:700;padding:0.7rem 1.5rem;border-radius:6px;text-decoration:none">
          {$btn_label}
        </a>
        <p style="color:#8b949e;font-size:0.78rem;margin-top:1.5rem">
          If you did not request this, you can safely ignore this email.
        </p>
      </div>
    </body>
    </html>
    HTML;
}
