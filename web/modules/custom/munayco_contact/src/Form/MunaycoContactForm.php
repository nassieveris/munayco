<?php

namespace Drupal\munayco_contact\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\munayco_contact\Service\PHPMailerService;

/**
 * Provides a custom contact form for the Munayco Contact module.
 */
class MunaycoContactForm extends FormBase {

  /**
   * The PHPMailer service.
   *
   * @var \Drupal\munayco_contact\Service\PHPMailerService
   */
  protected $phpMailerService;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new MunaycoContactForm.
   *
   * @param \Drupal\munayco_contact\Service\PHPMailerService $php_mailer_service
   *   The PHPMailer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(PHPMailerService $php_mailer_service, ConfigFactoryInterface $config_factory) {
    $this->phpMailerService = $php_mailer_service;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('munayco_contact.phpmailer_service'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'munayco_contact_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add a field for "Origen".
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Origen'),
      '#required' => TRUE,
      '#default_value' => '',
    ];

    // Add a field for "Nombre y Apellidos".
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre y Apellidos'),
      '#required' => TRUE,
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => $this->t('ej. Gia Brunela'),
      ],
    ];

    // Add a field for "Email".
    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => $this->t('ej. gia.brunela@gmail.com'),
      ],
    ];

    // Add a numeric field for "Teléfono".
    $form['telephone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Teléfono'),
      '#required' => TRUE,
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => $this->t('ej. 987789987'),
      ],
    ];

    // Add a field for "Explícanos tu caso".
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Explícanos tu caso'),
      '#required' => TRUE,
      '#default_value' => '',
      '#attributes' => [
        'placeholder' => $this->t('Detalla tu caso'),
      ],
    ];

    // Add a submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enviar'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Prepare email content.
    $email_body = implode("\n", [
      'Nombre y Apellidos: ' . $form_state->getValue('name'),
      'Email: ' . $form_state->getValue('mail'),
      'Teléfono: ' . $form_state->getValue('telephone'),
      'Mensaje: ' . $form_state->getValue('message'),
    ]);

    $subject = 'Munayco Web - Correo enviado desde la web: ' . $form_state->getValue('subject');
    $from = 'pablo.nassi@gmail.com';

    // Retrieve SMTP configuration from smtp.settings.yml.
    $smtp_config = $this->configFactory->get('smtp.settings');
    $smtp_host = $smtp_config->get('smtp_host');
    $smtp_port = $smtp_config->get('smtp_port');
    $smtp_user = $smtp_config->get('smtp_username');
    $smtp_pass = $smtp_config->get('smtp_password');

    // Define the recipients.
    $recipients = [
      'arkyweb.peru@gmail.com',
      'gino.nassi@gmail.com',
    ];

    // Send email to each recipient.
    $email_errors = [];
    foreach ($recipients as $to) {
      if (!$this->phpMailerService->sendEmail($to, $subject, $email_body, $from, $smtp_host, $smtp_port, $smtp_user, $smtp_pass)) {
        $email_errors[] = $to;
      }
    }

    // Handle success or failure.
    if (empty($email_errors)) {
      $this->messenger()->addMessage($this->t('El formulario se ha enviado correctamente.'));
    }
    else {
      $this->messenger()->addError($this->t('No se pudo enviar el correo a las siguientes direcciones: @emails', [
        '@emails' => implode(', ', $email_errors),
      ]));
    }
  }

}
