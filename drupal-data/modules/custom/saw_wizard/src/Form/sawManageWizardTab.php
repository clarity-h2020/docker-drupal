<?php

namespace Drupal\saw_wizard\Form;

use Drupal\field_ui\Form\EntityDisplayFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

class sawManageWizardTab extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'saw_wizard_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bundle = NULL, $entity_type='node') {
    
    $form['#bundle']= $bundle;
    $form['#entity_type']= $entity_type;
    $config = \Drupal::config('saw_wizard.settings');

    $stored_wizard = $config->get($entity_type.'.'.$bundle);
    $form_modes = \Drupal::service('entity_display.repository')->getFormModeOptionsByBundle($entity_type,$bundle);
    
    $steps=[];

  
    $form['wizard_selection'] = array(
      '#type'=>'field_ui_table',
      '#attributes' => array('id' =>'saw-wizard-create'),
      '#header'=>array('Form mode','Title','weight','parent'),
      '#rows'=>$steps,
    );
    $form['wizard_selection']['#tree'] = TRUE;
    $form['wizard_selection']['#tabledrag'][]= [
      'action'=>'order',
      'relationship'=>'sibling',
      'group'=>'step-weight',
    ];
    $form['wizard_selection']['#tabledrag'][]= [
      'action' => "match",
      'relationship' => "parent",
      'group' => "step-parent",
      'subgroup' => "step-parent",
      'source' => "step-name",
    ];
    $form['wizard_selection']['#tabledrag'][]= [
      'action' => "match",
      'relationship' => "parent",
      'group' => "step-region",
      'subgroup' => "step-region",
      'source' => "step-name",
    ];
    
    /*
     * Selected Enabled and disabled "Fields"
     */
     
    //$form['wizard_selection']['used']['#tree']=true;
    $form['wizard_selection']['used']['label']=array('#type'=>'item','#markup' => 'used in wizard',);
    $form['wizard_selection']['used']['#attributes']['class'][]='tabledrag-root';
    $form['wizard_selection']['used']['#attributes']['class'][] = 'draggable';
    $form['wizard_selection']['used']['#nodrag'] = TRUE;
    $form['wizard_selection']['used']['title']=array(
          '#type'=>'label',
          '#title' => 'Forms used in Wizard',
        );
  
        $form['wizard_selection']['used']['weight'] = array(
          '#type' => 'textfield',
          '#title' => t('Weight for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => '0',
          '#attributes' => array('class' => array('step-weight')),
          '#size' => 3,
        );
        $form['wizard_selection']['used']['parent_wrapper']['parent'] = array(
          '#type' => 'textfield',
          '#title' => t('parent for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => $stored_wizard['steps'][$stepname]['parent'],
          '#attributes' => array('class' => array('step-parent')),
        );
        $form['wizard_selection']['used']['parent_wrapper']['stepname']=array('#type'=>'hidden','#default_value' => 'used','#attributes'=>array('class'=>'step-name'),'#title_display' => 'invisible',);
        
        
    $form['wizard_selection']['notused']['label']=array('#type'=>'item','#markup' => 'not used in wizard',);
    $form['wizard_selection']['notused']['#attributes']['class'][]='draggable';
    $form['wizard_selection']['notused']['#attributes']['class'][]='tabledrag-root';
    $form['wizard_selection']['notused']['title']=array(
            '#type'=>'label',
            '#title' => 'Forms not used in Wizard'
            //'#default_value' => $stored_wizard[$stepname]['title'],
          );
        $form['wizard_selection']['notused']['weight'] = array(
          '#type' => 'textfield',
          '#title' => t('Weight for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => '1',
          '#attributes' => array('class' => array('step-weight')),
          '#size' => 3,
        );
        $form['wizard_selection']['notused']['parent_wrapper']['parent'] = array(
          '#type' => 'textfield',
          '#title' => t('parent for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => $stored_wizard['steps'][$stepname]['parent'],
          '#attributes' => array('class' => array('step-parent')),
        );
        $form['wizard_selection']['notused']['parent_wrapper']['stepname']=array('#type'=>'hidden','#default_value' => 'notused','#attributes'=>array('class'=>'step-name'),'#title_display' => 'invisible',);
      
    
    /*
     *
     */
    foreach ($form_modes as $stepname => $step){  
      
      $form['wizard_selection'][$stepname]['#attributes']['class'][]='draggable';
      $form['wizard_selection'][$stepname]['label']=array('#type'=>'item','#markup' => $step,);
     
      if(array_key_exists ( $stepname , $stored_wizard['steps'])){

        $form['wizard_selection'][$stepname]['title']=array(
          '#type'=>'textfield',
          '#default_value' => $stored_wizard['steps'][$stepname]['title'],
        );
      
        $form['wizard_selection'][$stepname]['weight'] = array(
          '#type' => 'textfield',
          '#title' => t('Weight for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => $stored_wizard['steps'][$stepname]['weight'],
          '#attributes' => array('class' => array('step-weight')),
          '#size' => 3,
        );
        $form['wizard_selection'][$stepname]['parent_wrapper']['parent'] = array(
          '#type' => 'textfield',
          '#title' => t('parent for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => $stored_wizard['steps'][$stepname]['parent'],
          '#attributes' => array('class' => array('step-parent')),
        );
        $form['wizard_selection'][$stepname]['parent_wrapper']['stepname']=array('#type'=>'hidden','#default_value' => $stepname,'#attributes'=>array('class'=>'step-name'),'#title_display' => 'invisible',);
      
      } else {
        $form['wizard_selection'][$stepname]['title']=array(
          '#type'=>'textfield',
          '#default_value' => '',
        );
     
        $form['wizard_selection'][$stepname]['weight'] = array(
          '#type' => 'textfield',
          '#title' => t('Weight for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => 0 ,
          '#attributes' => array('class' => array('step-weight')),
        );
        $form['wizard_selection'][$stepname]['parent_wrapper']['parent'] = array(
          '#type' => 'textfield',
          '#title' => t('parent for @title', array('@title' => $stepname)),
          '#title_display' => 'invisible',
          '#default_value' => 'notused',
          '#attributes' => array('class' => array('step-parent')),
        );
        $form['wizard_selection'][$stepname]['parent_wrapper']['stepname']=array('#type'=>'hidden','#default_value' => $stepname,'#attributes'=>array('class'=>'step-name'),'#title_display' => 'invisible',);
      
      }
    }
    
    $form['nav_type'] = array(
      '#type' => 'radios',
      '#title' => 'Navigation type',
      '#default_value' => $stored_wizard['nav_type'],
      '#options' => array(
        0 => 'Menu navigation',
        1 => 'Tree navigation',
      ),
    );
    
    $form['wizard_active'] = array(
      '#type'=> 'checkbox',
      '#title' => 'Activate Wizard',
      '#description' => 'If activated the wizard navigation is shown on viewing used form/view modes',
    );
    if(array_key_exists ('active' , $stored_wizard)){
      if($stored_wizard['active']==1){
        $form['wizard_active']['#default_value'] = TRUE;  
      }
       
    }
    
    
    
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    //$form['actions']['submit']['#submit'][]='saw_wizard_save_step_order';
    return $form;
  }
  

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $settings= array();
    $bundle = $form['#bundle'];
    $entity_type = $form['#entity_type']; 
    
   
    // Save settings
    $wizard = $form_state->getValues()['wizard_selection'];
    
    
    foreach ($wizard as $stepname => $step){
      $settings['steps'][$stepname] = array(
        'title' => $step['title'],
      //  'use' => $step['use'],
        'weight' => $step['weight'],
        'parent' => $step['parent_wrapper']['parent']
      );
    }
    $settings['active'] = $form_state->getValues()['wizard_active'];
    $settings['nav_type'] = $form_state->getValues()['nav_type'];
    
    $config = \Drupal::service('config.factory')->getEditable('saw_wizard.settings');
    $config->set($entity_type.'.'.$bundle,$settings)->save();
    
    
    
     // Parameters for redirect route and redirect
    switch ($entity_type){
      case 'node':
        $tab ='';
        $type = 'node_type';
        break;
      case 'group' :
        $tab ='_group';
        $type = 'group_type';
        break;
      case 'media' :
        $tab ='_media';
        $type = 'media_type';
        break;
      case 'taxonomy_term' :
        $tab ='_vocabulary';
        $type = 'taxonomy_vocabulary';
        break;
    }
    
    $form_state->setRedirect(
      'saw_wizard.tab'.$tab,
      [$type => $bundle]
    );


  }


}
