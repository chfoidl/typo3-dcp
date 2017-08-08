# TYPO3 Extension ``DCP``

[![StyleCI](https://styleci.io/repos/98464032/shield?branch=master)](https://styleci.io/repos/98464032)
[![Latest Stable Version](https://poser.pugx.org/sethorax/typo3-dcp/v/stable)](https://packagist.org/packages/sethorax/typo3-dcp)
[![License](https://poser.pugx.org/sethorax/typo3-dcp/license)](https://packagist.org/packages/sethorax/typo3-dcp)


> This extension adds a plugin to display centralized tt_content elements based on storage (PIDs).

### Features

- Works with all content elements (even third party ones like DCEs)!
- Outputs all content elements from selected pages (or sysfolders).
- Ability to define a plugin mode whose value can be retreived in content element templates via a built-in viewhelper.
- Ability to further filter the elements by TYPO3 categories
- Ability to limit the number of records.
- Ability to select a tt_content database field used for sorting.
- Ability to select the sorting direction.
- Fully compatible with multiple languages and all language overlay modes.

> Say hello to centralized dynamic content!

Rendering is done via the content object typoscript methods. So all default functionality like **start and end times** and **hidden** does work!


### Usage

#### Installation

Installation using Composer

It is recommended to install this extension via composer.  
To install it just do ``composer require sethorax/typo3-dcp``

This extension can also be installed traditionally via the TYPO3 Extension Repository (TER).

#### Setup

1. Include the static TypoScript template of the extension.
2. Define some plugin modes via pageTS (see example below).
3. Create the dynamic content plugin where you want to output your centralized content elements.
4. Set your mode, and storage and optionally some categories, the limit, order field and sorting direction.
5. Have fun with your centralized content!


### Example

The first thing before using this plugin is to specify the plugin modes in pageTS.  
To do that simply add a new pageTS settings like the following: 

```
tx_dcp.pluginModes {
	list = List
	detail = Detail
}
```

Once the plugin modes are set, they can be selected within the plugin settings.  
With the help of the built-in ```{dcp:mode.get()}``` viewhelper the plugin mode value can be retreived in the content element template.  
This makes it possible to create different render options for the same content element type based on the plugin mode (e.g. list or detail view).

##### ViewHelper namespace

To use the built-in viewhelper, simply add the following at the top of your fluid template:  
> ```{namespace dcp=Sethorax\Dcp\ViewHelpers}```
