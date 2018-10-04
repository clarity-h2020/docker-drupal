<?php
namespace Drupal\help_widgets\Plugin\Field\FieldWidget;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'textarea_widget' widget.
 *
 * @FieldWidget(
 *   id = "textarea_widget",
 *   label = @Translation("Widget for contextual help"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class TextareaWidget extends WidgetBase
{

    /**
     *
     * {@inheritdoc}
     */
    public static function defaultSettings()
    {
        return [
            'rows' => 6,
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

        // $elements['size'] = [
        // '#type' => 'number',
        // '#title' => t('Size of textfield'),
        // '#default_value' => $this->getSetting('size'),
        // '#required' => TRUE,
        // '#min' => 1,
        // ];
        $elements['rows'] = [
            '#type' => 'number',
            '#title' => t('Rows'),
            '#default_value' => $this->getSetting('rows'),
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

        $summary[] = t('Number of rows: @rows', [
            '@rows' => $this->getSetting('rows')
        ]);
        $placeholder = $this->getSetting('placeholder');
        if (! empty($placeholder)) {
            $summary[] = t('Placeholder: @placeholder', [
                '@placeholder' => $placeholder
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

//             $url = Url::fromUri('internal:' . $this->getSetting('url_help'));
//             $project_link = Link::fromTextAndUrl(t('Open Project'), $url);
//             $project_link = $project_link->toRenderable();
//             // If you need some attributes.
//             $project_link['#attributes'] = array('class' => array('button', 'button-action', 'button--primary', 'button--small'));
//             $element['link_help'] = $project_link;

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
            '#type' => 'textarea',
            '#default_value' => $items[$delta]->value,
            '#rows' => $this->getSetting('rows'),
            '#placeholder' => $this->getSetting('placeholder'),
//             '#field_suffix' => array(
//                 $link['link_help'], $link['link_example']
//             ),
            '#field_suffix' => $link,
            '#attributes' => [
                'class' => [
                    'js-text-full',
                    'text-full'
                ]
            ]
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
