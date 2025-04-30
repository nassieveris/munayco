<?php

namespace Drupal\munayco_contact\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Provides a service for sending emails using PHPMailer.
 */
class PHPMailerService {

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new PHPMailerService.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Sends an email using PHPMailer and SMTP.
   *
   * @param string $to
   *   The recipient email address.
   * @param string $subject
   *   The email subject.
   * @param string $body
   *   The email body.
   * @param string $from
   *   The sender email address.
   * @param string $smtp_host
   *   The SMTP server host.
   * @param int $smtp_port
   *   The SMTP server port.
   * @param string $smtp_user
   *   The SMTP username.
   * @param string $smtp_pass
   *   The SMTP password.
   *
   * @return bool
   *   TRUE if the email was sent successfully, FALSE otherwise.
   */
  public function sendEmail($to, $subject, $body, $from, $smtp_host, $smtp_port, $smtp_user, $smtp_pass) {
    $mail = new PHPMailer(true);

    try {
      // Disable verbose debug output for production.
      $mail->SMTPDebug = 0;

      // SMTP configuration.
      $mail->isSMTP();
      $mail->Host = $smtp_host;
      $mail->SMTPAuth = true;
      $mail->Username = $smtp_user;
      $mail->Password = $smtp_pass;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = $smtp_port;

      // Email headers.
      $mail->setFrom($from, 'Munayco Portal');
      $mail->addAddress($to);
      $mail->addReplyTo($from);

      // Add the additional message to the body.
      $current_date_time = date('d/m/Y H:i:s');
      $additional_message = "Señora Munayco, este mensaje ha sido enviado: $current_date_time. Estos son los datos recogidos:\n\n";
      $body = $additional_message . $body;

      // Email content.
      $mail->isHTML(false);
      $mail->Subject = $subject;
      $mail->Body = $body;

      // Send the email.
      $mail->send();

      // Log success message.
      $this->loggerFactory->get('munayco_contact')->info('Email sent successfully to @recipient.', [
        '@recipient' => $to,
      ]);

      return TRUE;
    }
    catch (Exception $e) {
      // Log detailed error information.
      $this->loggerFactory->get('munayco_contact')->error('Failed to send email to @recipient. Error: @error', [
        '@recipient' => $to,
        '@error' => $mail->ErrorInfo,
      ]);

      return FALSE;
    }
  }

}
