<?php

namespace Drupal\optionset\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'optionset' widget.
 *
 * @FieldWidget(
 *   id = "optionset_widget",
 *   label = @Translation("Optionset"),
 *   field_types = {
 *     "optionset"
 *   }
 * )
 */
class OptionsetWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_settings = $this->getFieldSettings();
    $options = $this->extractAllowedValues($field_settings['allowed_values']);
    $item =& $items[$delta];
    $value = $item->getOptions();
    $element['value'] = [
      '#type' => 'checkboxes',
      '#title' => $this->fieldDefinition->getLabel(),
      '#options' => $options,
      '#default_value' => $value,
      '#description' => $this->fieldDefinition->getDescription(),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $results = [];
      foreach ($item['value'] as $key => $value) {
        if (!empty($value)) {
          $results[] = $key;
        }
      }
      $item['value'] = implode('|', $results);
    }

    return $values;
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
  protected function extractAllowedValues($string) {
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

}
