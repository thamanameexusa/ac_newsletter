<?php
/**
 * @file
 * Contains \Drupal\ac_newsletter\Form\ActiveCampaignConnectConfigForm.
 */

namespace Drupal\ac_newsletter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

class ActiveCampaignConnectConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ac_newsletter_connect_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ActiveCampaign.Connection',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get the configuration settings.
    $config = $this->config('ActiveCampaign.Connection');

    // Add fieldset.
    $form['active_campaign'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('ActiveCampaign API connection settings'),
    ];

    // Field to provide ActiveCampaign API url.
    $form['active_campaign']['ac_newsletter_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#default_value' => $config->get('ac_newsletter_api_url'),
      '#required' => TRUE,
      '#size' => 70,
      '#description' => $this->t('Provide API url of your ActiveCampaign Account. ex: https://ACCOUNT.api-us1.com'),
      '#attributes' => ['placeholder' => $this->t('Enter ActiveCampaign API url')],
    ];

    // Field to provide ActiveCampaign API key.
    $url = Url::fromUri('base:' . drupal_get_path('module', 'ac_newsletter') . '/images/ac-api-tab.png', [
      'attributes' => ['target' => '_blank'],
      'absolute' => TRUE,
    ]);
    $apiTabScreenShotLink = Link::fromTextAndUrl($this->t('API tab'), $url)->toString();
    $form['active_campaign']['ac_newsletter_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API KEY'),
      '#default_value' => $config->get('ac_newsletter_api_key'),
      '#required' => TRUE,
      '#size' => 70,
      '#description' => $this->t('Provide API key of your ActiveCampaign Account. Check the !api of your account.', [
        '!api' => $apiTabScreenShotLink]),
      '#attributes' => ['placeholder' => $this->t('Enter ActiveCampaign API key')],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('ActiveCampaign.Connection')
      ->set('ac_newsletter_api_url', $values['ac_newsletter_api_url'])
      ->set('ac_newsletter_api_key', $values['ac_newsletter_api_key'])
      ->save();

    // Test the credentials.
    if (FALSE && _ac_newsletter_test_api_credentials()) {
      drupal_set_message($this->t('Successfully connected'));
    }
    else {
      drupal_set_message($this->t('Access denied: Invalid credentials (URL and/or API key).'), 'error');
    }
    parent::submitForm($form, $form_state);
  }
}
