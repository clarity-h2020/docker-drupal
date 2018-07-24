namespace Drupal\saw_wizard\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;

class SaveStep extends FormBase {

  public static function save (array &$form, FormStateInterface $form_state){


    $response=[
      '#type'=>'html_tag',
      '#tag'=>'div',
      '#value'=>'gespeichert',
      '#attributes'=>array('id'=>'ajax-response'),
    ];
    return $response;

  }
}
