<?php

namespace Drupal\optionset\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'optionset' formatter.
 *
 * @FieldFormatter(
 *   id = "optionset_formatter",
 *   label = @Translation("Optionset"),
 *   field_types = {
 *     "optionset"
 *   }
 * )
 */
class OptionsetFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $field_settings = $this->getFieldSettings();
    $options = $this->extractAllowedValues($field_settings['allowed_values']);
    foreach ($items as $delta => $item) {
      $value = $item->getValue();
      $results = [];
      foreach ($options as $option => $title) {
        $res = in_array($option, $value) ? '☑' : '☐';
        $results[] = $res . ' ' . $title;
      }
      $elements[$delta] = [
        '#markup' => implode(', ', $results) . '.',
      ];
    }

    return $elements;
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
