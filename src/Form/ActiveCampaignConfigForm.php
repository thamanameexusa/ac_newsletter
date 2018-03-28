<?php
/**
 * @file
 * Contains \Drupal\ac_newsletter\Form\ActiveCampaignConfigForm.
 */

namespace Drupal\ac_newsletter\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ActiveCampaignConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ac_newsletter_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ActiveCampaign.Configuration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('ActiveCampaign.Configuration');

    // Add fieldset.
    $form['active_campaign'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('ActiveCampaign API configuration settings'),
    ];

    // Get options(ActiveCampaign Lists).
    //$options = _ac_newsletter_get_lists();
    $options = ['Hello', 'World'];

    // Field to select/enable lists on the site.
    $form['active_campaign']['ac_newsletter_site_lists'] = [
      '#type' => 'select',
      '#title' => $this->t('Enable Lists on site'),
      '#options' => $options,
      '#default_value' => $config->get('ac_newsletter_site_lists'),
      '#multiple' => TRUE,
      '#required' => TRUE,
      '#description' => $this->t('Provides lists to be enabled on the site.'),
    ];

    // Optionals, Field to use Briteverify API on subscription form.
    $form['active_campaign']['ac_newsletter_use_briteverify_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Briteverify API'),
      '#default_value' => $config->get('ac_newsletter_use_briteverify_api'),
      '#description' => $this->t('Provides email verification using Briteverify API.'),
    ];

    // Field to provide API key of the Briteverify.
    $form['active_campaign']['ac_newsletter_briteverify_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Briteverify API Key'),
      '#attributes' => ['placeholder' => $this->t('Enter Briteverify API key')],
      '#size' => 50,
      '#default_value' => $config->get('ac_newsletter_briteverify_api_key'),
      '#description' => $this->t('Provide an valid API key of Briteverify.'),
      '#states' => [
        'invisible' => [
          'input[name="ac_newsletter_use_briteverify_api"]' => ['checked' => FALSE],
        ],
      ],
    ];

    // Optionals, Field to enable Double Opt-in on subscription form.
    $form['active_campaign']['ac_newsletter_enable_double_opt_in'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Double Opt-in'),
      '#default_value' => $config->get('ac_newsletter_enable_double_opt_in'),
      '#description' => $this->t('Opt-in emails are now directly related to subscription forms only. You may only add “unconfirmed” subscribers (subscribers that would be sent an opt-in) from a subscription form.'),
    ];

    // Field to provide the subscription form id.
    $form['active_campaign']['ac_newsletter_subscription_form_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription Form ID'),
      '#attributes' => ['placeholder' => $this->t('Enter Form ID')],
      '#size' => 20,
      '#default_value' => $config->get('ac_newsletter_subscription_form_id'),
      '#description' => $this->t('Provide an subscription form <em>form_id</em> where Double Opt-in is enabled'),
      '#states' => [
        'invisible' => [
          'input[name="ac_newsletter_enable_double_opt_in"]' => ['checked' => FALSE],
        ],
      ],
    ];

    // Optionals, Field to provide alter text for the lists.
    $form['active_campaign']['ac_newsletter_alternative_text'] = [
      '#title' => $this->t('Alternative Text for the lists'),
      '#type' => 'textarea',
      '#attributes' => ['placeholder' => $this->t('Enter Alternative text or leave blank')],
      '#default_value' => $config->get('ac_newsletter_alternative_text'),
      '#description' => $this->t('Provide alternative text per line for the enabled lists to display on my-account page. For Example: Daily Strip|The Daily Email (sent daily)'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get all values.
    $values = $form_state->getValues();

    // Validate the Briteverify credentials.
    if ($values['ac_newsletter_use_briteverify_api']) {
      if (empty($values['ac_newsletter_briteverify_api_key'])) {
        $form_state->setErrorByName('ac_newsletter_briteverify_api_key', t('Provide a valid BriteVerify API key'));
      }
      else {
        $response_data = _ac_use_brite_verify(trim($values['ac_newsletter_briteverify_api_key']));
        if ($response_data['code'] != 200) {
          $msg = $response_data['errors']['user'];
          $form_state->setErrorByName('ac_newsletter_briteverify_api_key', t('bpi.briteverify.com : @msg', array('@msg' => $msg)));
        }
        else {
          drupal_set_message(t('bpi.briteverify.com : Your API key authorization successful!'));
        }
      }
    }

    // Validate the ActiveCampaign Form ID.
    if ($values['ac_newsletter_enable_double_opt_in'] && empty($values['ac_newsletter_subscription_form_id'])) {
      $form_state->setErrorByName('ac_newsletter_subscription_form_id', t('Provide a valid ActiveCampaign Form ID'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('ActiveCampaign.Configuration')
      ->set('ac_newsletter_site_lists', $values['ac_newsletter_site_lists'])
      ->set('ac_newsletter_use_briteverify_api', $values['ac_newsletter_use_briteverify_api'])
      ->set('ac_newsletter_briteverify_api_key', $values['ac_newsletter_briteverify_api_key'])
      ->set('ac_newsletter_enable_double_opt_in', $values['ac_newsletter_enable_double_opt_in'])
      ->set('ac_newsletter_subscription_form_id', $values['ac_newsletter_subscription_form_id'])
      ->set('ac_newsletter_alternative_text', $values['ac_newsletter_alternative_text'])
      ->save();
    parent::submitForm($form, $form_state);
  }
}
