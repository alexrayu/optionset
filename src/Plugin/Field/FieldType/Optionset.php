<?php

namespace Drupal\optionset\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'optionset' field type.
 *
 * @FieldType(
 *   id = "optionset",
 *   label = @Translation("Optionset"),
 *   description = @Translation("A set of options"),
 *   default_widget = "optionset_widget",
 *   default_formatter = "optionset_formatter"
 * )
 */
class Optionset extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'allowed_values' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);

    // Target type.
    $form['allowed_values'] = [
      '#title' => t('Allowed values'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('allowed_values'),
      '#description' => $this->t('A set keys and values separated by "|", one per line.'),
      '#element_validate' => [[static::class, 'validateAllowedValues']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Count of entities in the list.
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Value'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'description' => 'Flat value',
          'type' => 'text',
          'not null' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->getValue());
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    if (empty($this->value) && !empty($this->values)) {
      return $this->values;
    }
    return explode('|', $this->value);
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];
    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');
    foreach ($list as $position => $text) {
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        $key = trim($matches[1]);
        $value = trim($matches[2]);
      }
      else {
        return NULL;
      }

      $values[$key] = $value;
    }

    return $values;
  }

  /**
   * Element_validate callback for options field allowed values.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateAllowedValues(array $element, FormStateInterface $form_state) {
    $values = static::extractAllowedValues($element['#value']);
    if (!is_array($values)) {
      $form_state->setError($element, t('Allowed values list: invalid input.'));
    }
    else {
      foreach ($values as $key => $value) {
        if ($error = static::validateAllowedValue($key)) {
          $form_state->setError($element, $error);
          break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    if (!is_string($option)) {
      return t('Options must be strings.');
    }

    return NULL;
  }

}
