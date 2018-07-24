<?php

namespace Drupal\saw_wizard\Controller;

use Drupal\saw_wizard\Form\sawManageWizardTab;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;

class sawWizard extends ControllerBase {
  
   /**
   * Provide arguments for sawManageWizardTab.
   *
   * @param string $node_type
   *   Node type.
   *
   * @return array
   *   Form array.
   */
  public function manageTab($node_type) {
   $form = \Drupal::formBuilder()->getForm(sawManageWizardTab::class, $node_type, 'node');
   return $form;
  }
  
  public function manageTab_group($group_type) {
   $form = \Drupal::formBuilder()->getForm(sawManageWizardTab::class, $group_type, 'group');
   return $form;
  }
  
  public function manageTab_media($media_type) {
   $form = \Drupal::formBuilder()->getForm(sawManageWizardTab::class, $media_type, 'media');
   return $form;
  }
  
  public function manageTab_vocabulary($taxonomy_vocabulary) {
   $form = \Drupal::formBuilder()->getForm(sawManageWizardTab::class, $taxonomy_vocabulary, 'taxonomy_term');
   return $form;
  }
}
