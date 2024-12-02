<?php

namespace Classes;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Alert
{
    /**
     * Envoi d'un mail
     *
     * @param string $subject Sujet du message
     * @param string $message Corps du message
     * @param string|array $to From:
     * @param string|array $cc Cc:
     * @param string|array $bcc Bcc:
     * @param array $files Les fichiers à attacher
     * @return boolean
     */
    public static function mail(string $subject, string $message, $to, $cc = [], $bcc = [], array $files = []): bool
    {
        $to = Alert::cleanMailAddress($to);
        $cc = Alert::cleanMailAddress($cc);
        $bcc = Alert::cleanMailAddress($bcc);

        if (empty($to)) {
            return false;
        }

        try {
            // Connexion SMTP
            $mail = new PHPMailer(true);
            $mail->isSMTP();

            $mail->Host = gethostbyname(getenv('APP_SMTP_HOSTNAME'));
            $mail->Port = getenv('APP_SMTP_PORT') ?: 25;
            $mail->Timeout = 30;

            if ($mail->Port == 25) {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = false;
            }

            if (!empty(getenv('APP_SMTP_USERNAME'))) {
                $mail->SMTPAuth = true;
                $mail->Username = getenv('APP_SMTP_USERNAME');
                $mail->Password = getenv('APP_SMTP_PASSWORD');
            } else {
                $mail->SMTPAuth = false;
            }

            // En-tête de mail
            $mail->setFrom('noreply@cicd.biz', 'CICD');
            $mail->addReplyTo('support-admin@cicd.biz', 'Support CICD');
            $mail->MessageID = '<' . bin2hex(random_bytes(16)) . '@cicd.biz>';

            foreach ($to as $email) {
                $mail->addAddress($email);
            }

            foreach ($cc as $email) {
                $mail->addCC($email);
            }

            foreach ($bcc as $email) {
                $mail->addBCC($email);
            }

            foreach ($files as $name => $file) {
                if (preg_match('/^\d+$/', $name)) {
                    $mail->addAttachment($file);
                } else {
                    $mail->addAttachment($file, $name);
                }
            }

            // Structure du mail
            $mail->isHTML();
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Envoi
            return $mail->send();
        } catch (Exception $e) {
            // ...
        }

        return false;
    }

    /**
     * Nettoyage des adresses mails
     *
     * @param string|array $mail Les adresses mails
     * @return array
     */
    public static function cleanMailAddress($mail): array
    {
        $mails = is_array($mail) ? $mail : explode(',', $mail);
        $returnMail = [];

        foreach ($mails as $mail) {
            if (filter_var(trim($mail), FILTER_VALIDATE_EMAIL)) {
                $returnMail[] = trim($mail);
            }
        }

        return $returnMail;
    }
}
