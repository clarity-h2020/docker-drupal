# Documentation Wizard by Form modes 

## Short description

The module allows the creation of wizards for adding/editing and viewing entities. So editors can fill fields of the node step by step, and site visitors can view nodes in the same „parts“ as the they were created. Therefore it uses form Modes and view modes to create the singels steps.

# Features

*Wizard in edit and view mode
* Wizard steps defined by view modes of edit form and viewed entity
* selection of display modes used in the wizard
* drag and drop interface for wizard creation
* Full navigation on top of the steps
* previous and next links on the bottom of steps
* Edit link to steps edit mode on bottom of step in view mode

## Requirements

1. Drupal 8.x
2. Form mode manager (https://www.drupal.org/project/form_mode_manager)
3. View mode Page (https://www.drupal.org/project/view_mode_page), if wizard in view mode is wanted

## Installation

Install the .zip/.tar.gz file in the modules folder as used from other modules

## Usage
### Preparation of steps

1. Create view modes for forms and viewed entities. Since step naming happens on content type level, you can use generic names like step1, step2,… for the form modes. So you can use them for more than one content type.
2. Assign view modes of Form and Entity to the content you want to "wizardise"
3. Configure View mode Page. When you configure the view mode paths (configuration/search and meta data/ view mode page) please use the same path pattern for each step (e.g. /%/view/{stepname}) and do not use “/%/stepname”. this conflicts with form mode manager and the edit pages will not be reachable any more.

### Generate wizard

On the pages admin/structure/types/manage/{your content type} you will find a new tab beside the Edit, Manage fields,… tabs called “Manage Wizard”. 
1. Drag the view modes into your desired Order as children of "used in wizard". You can also create sub steps. View modes not used in the wizard has to be childs of "not used in wizard". 
2. Give your steps Titles by using the Title column. These titles will be used in the navigation to identify the steps
3. Choose whisch navigation is used.
  * menu navigation will show a navigation like a page menu. The first levels are always shown, sub levels are only shown if the current level is the parent of the sub levels.
  * tree navigation will show you the step tree. All sub stepps are accessable. The substeps of the current step are expanded.
4. The wizard is used if  “Activate wizard” is checked. Then the wizard navigation is inserted every time a display mode used in the wizard is viewed.

##  Styling

The wizard is delivered with an basic styling of its parts for functionality. Use following classes to make a nicer view:
* wizard-container: the container of the wizard
* nav-menu: class of menu navigation
* nav-tree: class of tree navigation
* in-path: current step an its parrent steps
* inactive: all other steps
* has-substeps: step with sub steps
* open-toggle: the + - part of the tree navigation is also present but not used in the menu navigation
* item-list every level of navigation

##  ToDo/plans for future

Feature/bug|Status of idea
-----------|--------------
Disabling of drag and drop steps outside of “used in wizard” and “not used in wizard” | Just to implement
explicitly enable wizard on viewing node. Now wizard parts are shown if view mode has name of step. | just to implement
possibility to copy Form mode to view mode, so steps have not to be assembled twice (at least have field in view mode, just rearrange them ) | Feasibility research possible to 75%
Wizard navigation and links (prev, next) as blocks. It would be easier to place them individually (prev, next on top e.g.) | Thinking about it sure it works, but how?


