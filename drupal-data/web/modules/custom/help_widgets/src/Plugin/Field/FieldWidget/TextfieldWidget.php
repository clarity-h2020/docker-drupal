<?php
namespace Drupal\help_widgets\Plugin\Field\FieldWidget;

use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'textfield_widget' widget.
 *
 * @FieldWidget(
 *   id = "textfield_widget",
 *   label = @Translation("Widget for contextual help"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TextfieldWidget extends WidgetBase
{

    /**
     *
     * {@inheritdoc}
     */
    public static function defaultSettings()
    {
        return [
            'size' => 60,
            'placeholder' => '',
            'url_help' => '',
            'url_example' => '',
        ] + parent::defaultSettings();
    }

    /**
     *
     * {@inheritdoc}
     */
    public function settingsForm(array $form, FormStateInterface $form_state)
    {
        $elements = [];

        $elements['size'] = [
            '#type' => 'number',
            '#title' => t('Size of textfield'),
            '#default_value' => $this->getSetting('size'),
            '#required' => TRUE,
            '#min' => 1
        ];
        $elements['placeholder'] = [
            '#type' => 'textfield',
            '#title' => t('Placeholder'),
            '#default_value' => $this->getSetting('placeholder'),
            '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.')
        ];

        $elements['url_help'] = [
            '#type' => 'textfield',
            '#title' => t('URL to help text'),
            '#default_value' => $this->getSetting('url_help'),
            '#description' => t('Link to the help text that will be shown to the user for this element. Type in the internal URL, e.g. /node/1'),
            '#element_validate' => [
                [static::class, 'validate'],
            ],
        ];

        $elements['url_example'] = [
            '#type' => 'textfield',
            '#title' => t('URL to example'),
            '#default_value' => $this->getSetting('url_example'),
            '#description' => t('Link to an actual example that will be shown to the user for this element. Type in the internal URL, e.g. /node/1'),
            '#element_validate' => [
                [static::class, 'validate'],
            ],
        ];

        return $elements;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function settingsSummary()
    {
        $summary = [];

        $summary[] = t('Textfield size: @size', [
            '@size' => $this->getSetting('size')
        ]);
        if (! empty($this->getSetting('placeholder'))) {
            $summary[] = t('Placeholder: @placeholder', [
                '@placeholder' => $this->getSetting('placeholder')
            ]);
        }

        return $summary;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state)
    {
        $link = array ();

        if ($this->getSetting('url_help')) {
            $link['link_help'] = [
                '#title' => $this->t(''),
                '#type' => 'link',
                //'#url' => Url::fromRoute('entity.node.canonical', ['node' => $this->getSetting('url_help')]),
                '#url' => Url::fromUri('internal:' . $this->getSetting('url_help')),
                '#attributes' => array(
                    'class' => array(
                        'contextual-help help-icon use-ajax',
                    ),
                    'data-dialog-type' => array(
                        'modal'
                    ),
                    'data-dialog-options' => array(
                        '{"width":700,"dialogClass":""}'
                    ),
                ),
            ];
        }

        if ($this->getSetting('url_example')) {
            $link['link_example'] = [
                '#title' => $this->t(''),
                '#type' => 'link',
                //'#url' => Url::fromRoute('entity.node.canonical', ['node' => $this->getSetting('url_example')]),
                '#url' => Url::fromUri('internal:' . $this->getSetting('url_example')),
                '#attributes' => array(
                    'class' => array(
                        'contextual-help example-icon use-ajax',
                    ),
                    'data-dialog-type' => array(
                        'modal'
                    ),
                    'data-dialog-options' => array(
                        '{"width":700,"dialogClass":""}'
                    ),
                ),
            ];
        }

        $element['value'] = $element + [
            '#type' => 'textfield',
            '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
            '#size' => $this->getSetting('size'),
            '#placeholder' => $this->getSetting('placeholder'),
            '#maxlength' => $this->getFieldSetting('max_length'),
            //'#field_suffix' => '<span> Info: ' . $this->getSetting('url_help') . ' - ' . $this->getSetting('url_example') . '</span>',
            '#field_suffix' => $link,
        ];

        return $element;
    }

    /**
     * Check if path exists
     */
    public static function validate($element, FormStateInterface $form_state) {
        $path = $element['#value'];
        $validator = \Drupal::service('path.validator');

        // if path not valid show error message to admin
        if(!$validator->isValid($path)) {
            $form_state->setError($element, t("The URL doesn't exist. Please fill in a valid URL in the form of /node/1"));
        }
    }
}
