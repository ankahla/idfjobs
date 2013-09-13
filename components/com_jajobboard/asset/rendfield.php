<?php
/**
 * ------------------------------------------------------------------------
 * JA Job Board Package For J25
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

function rendfieldlabel($field) {
return (isset($field->label_ml) && !empty($field->label_ml)) ? $field->label_ml : (isset($field->text)?$field->text:$field->label);
}

/**
 * Generate HTML string for specific field
 *
 
 * @param object  $item   Field item
 * @param string  $field    Field data
 * @param boolean $event    Add event to string
 * @param boolean $edit   Is edit mode
 * @param boolean $viewonly View only type
 * @param boolean   $view   Show on view mode
 * @param boolean $order_item Order item by
 *
 * @return string
 * */
function rendfield($item, $field, $event = false, $edit = false, $viewonly = false, $view = null, $order_item = null)
{
	global $option, $jbconfig;
	if (!isset($field->field_name)) {
		return;
	}

	$mainframe = JFactory::getApplication();
	
	//multilingual
	if(isset($field->field_comment_ml) && !empty($field->field_comment_ml)) {
		$field->field_comment = $field->field_comment_ml;
	}
	if(isset($field->field_example_ml) && !empty($field->field_example_ml)) {
		$field->field_example = $field->field_example_ml;
	}

	
	$isSpecialTables = ($field->referred_table == 'ja_categories' || $field->referred_table == 'ja_location') ? 1 : 0;


	if ($field->field_type != 'CHECKBOXLIST' && $field->field_type != 'MSELECT') {
		if (is_string($item->{$field->field_name})) {
			$item->{$field->field_name} = htmlspecialchars_decode($item->{$field->field_name});
			$item->{$field->field_name} = html_entity_decode($item->{$field->field_name});
		}
	}

	$r = null;
	$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

	JHTML::_('behavior.tooltip');
	JHTML::_('behavior.mootools');
	JHTML::_('behavior.calendar');
	// Load the form validation behavior
	JHTML::_('behavior.formvalidation');

	if (($field->eventmouse != null) && ($field->eventmouse != '')) {
		if ($field->functionjs != null) {
			$mouse_event = $field->eventmouse . "='" . $field->functionjs . "'";
		} else {
			$mouse_event = '';
		}
	} else {
		$mouse_event = '';
	}

	$className = (($field->field_class != null) && ($field->field_class != '')) ? $field->field_class : 'inputbox';
	if($field->is_required) {
		$className .= ' required';
	}
	$class = 'class="'.$className.'"';

	if ($view == null) {
		$view = JRequest::getCmd('view');
	}

	$javascript = '';

	if ($event) {
		$javascript = 'onchange="form.submit();"';
	}

	if (isset($item->id) && (int) $item->id != 0) {
		$value = $item->{$field->field_name};
	} else {
		$value = $item->{$field->field_name} ? $item->{$field->field_name} : $field->field_init;
	}

	//  $value = str_replace("'"," &rsquo;", $value);
	$user = JFactory::getUser();
	$isAdministrator = JBAccess::isInManagerGroup($user->id);
	
	$comment = $example = '';
	if ($field->field_comment != '') {
		$comment = ' <span class="editlinktip hasTip" title="' . rendfieldlabel($field) . '::' . str_replace('"', "'", $field->field_comment) . '"><img border="0" alt="Tooltip" src="' . $url . '/components/'.JBCOMNAME.'/images/tooltip.png" /></span>';
	}

	if ($field->field_example != '') {
		$example = $field->field_example;
	}

	if ($viewonly && $field->display == 'hide_on_view') {
		return;
	}

	if ($field->is_referred && ($field->field_type == "MSELECT" || $field->field_type == "SELECT")) {

		$r = getExternalCombo($field, $value, $edit, $viewonly, $javascript, 1, $class, $mouse_event);

		if (!$viewonly && !$field->is_readonly) {
			$r .= JText::_($comment);
		}

		if(!$isSpecialTables) {
			if (($field->is_required == '1') && (!$viewonly) && (!$field->is_readonly) && !$javascript && $field->display != "hide_on_new") {
				$r .= '<span id="req-'.$field->field_name.'" class="ja-field-require" style="color:red;">*</span>';
			}
		}

		if (!$viewonly && $example != '') {
			$r .= '<br/>' . JText::_($example);
		}
	} else if ((($edit && in_array($field->display, array('hide_on_edit', 'hide_on_new'))) || $viewonly) && ($field->display != 'admin_only' || $isAdministrator) && $field->display != 'hide_all') {
		switch (trim($field->field_type)) {
			case 'BOOLEAN':
				$r = $value ? JText::_('JYES') : JText::_('JNO');
				break;

			case 'IMAGE':
				if ($value) {
					$src = JURI::base() . $value;
					$temp_path = str_replace('\\', DS, JPATH_SITE . DS . $value);
					$temp_path = str_replace('/', DS, $temp_path);

					if (file_exists($temp_path)) {
						if (intval($field->field_width)) {
							//$size = "width='{$field->field_width}'";
							$size="";
							if (intval($field->field_height)) {
								$size .= " height='{$field->field_height}'";
							}
						} else {
							$imgInfo = getimagesize($temp_path);
							$size = "width='{$imgInfo [0]}' height='{$imgInfo [1]}'";
						}

						$r = "<img $class src=\"$src\" alt='photo' $size />";
					}
				}

				if ($r == '') {
					$size = '';
					if (intval($field->field_width)) {
						//$size = "width='{$field->field_width}'";
						$size="";

						if (intval($field->field_height)) {
							$size .= " height='{$field->field_height}'";
						}
					}

					$src = JURI::base() . 'components/com_jajobboard/images/no-image.gif';
					$r = "<img $class src=\"$src\" alt=\"photo\" $size />";
				}

				break;

			case 'URL':
				if ($value && substr($value, 0, 7) != 'http://') {
					$value = 'http://' . $value;
				}

				$r = "<a target='_blank' href='" . $value . "'>" . $value . "</a>";
				break;

			case 'FILE':
				if ($value) {
					$file_name = explode('/', $value);

					if (!$view) {
						$view = JRequest::getCmd('view', '');
					}

					$ext = strtolower(substr($value, -3, 3));

					if (!file_exists(JPATH_SITE.'/components/com_jajobboard/images/icons/applications/'.$ext . '.gif')) {
						$ext = 'unknow';
					}

					$file_name = explode('/', $value);
					$r = '<img src="components/com_jajobboard/images/icons/applications/' . $ext . '.gif" alt="thumbnail"/>';
					$r .= "<a href='" . JRoute::_( 'index.php?option=' . JBCOMNAME . '&view=' . $view . '&task=download&field_name=' . $field->field_name . '&cid[]=' . $item->id) . "'>" . JText::_(substr($file_name[count($file_name) - 1], 14)) . '</a>';
				}
				break;

			case 'SELECT':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);

				foreach ($arr_option as $options) {
					if ($options->value == $value) {
						$r .= $options->text;
					}
				}
				break;

			case 'CHECKBOXLIST':
				JRequest::setVar('field_id', $field->table_name . '_' . $field->field_name);
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name, $value);
				$arr_value = array();

				foreach ($arr_option as $options) {
					$arr_value[] = $options->text;
				}

				$r = implode(', ', $arr_value);
				break;

			case 'MSELECT':
				if (!empty($value)) {
					$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name, $value);
				} else {
					$arr_option = array();
				}

				$arr_value = array();

				foreach ($arr_option as $options) {
					$arr_value[] = $options->text;
				}

				$r = implode(', ', $arr_value);

				if ($field->field_name == 'apply_type') {
					$value = explode(',', $value);
					array_filter($value, 'trim');

					if (in_array('1', $value)) {
						$r .= '&nbsp<span>(<a target="_blank" href="' . $item->direct_url . '">' . $item->direct_url . '</a>)</span>';
					}
				}
				break;

			case 'RADIO':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name, $value);
				$arr_value = array();

				foreach ($arr_option as $options) {
					$arr_value[] = $options->text;
				}

				$r = implode(', ', $arr_value);

			case 'INTEGER':
				if ($field->is_referred && $item->{$field->field_name} != "") {
					$db = JFactory::getDbo();
					$sql = "SELECT {$field->referred_value} FROM #__{$field->referred_table} WHERE {$field->referred_key} = '" . $item->{$field->field_name} . "'";
					$db->setQuery($sql);
					$r .= $db->loadResult();
				}

				break;

			case 'CHECK':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name, $value);
				$arr_value = array();

				foreach ($arr_option as $options) {
					$arr_value[] = $options->text;
				}

				$r = implode(', ', $arr_value);
				break;

			case 'DATE':
				if (!$value > 0) {
					$r = 'N/A';
				} else {
					$r = generateDate($value);
				}
				break;

			case 'DATE_CAL':
				$r = generateDate($value);
				break;

			case 'SEPERATOR':
				$r = rendfieldlabel($field);
				break;

			default:
				break;
		}

		if (!$r && trim($field->field_type) != 'IMAGE') {
			if (trim($field->field_type) == "EDITOR") {
				$r = $value;
			} else {
				$r = str_replace("\n", '<br/>', $value);
			}
		}

		if ($r && $field->field_name == 'job_tags') {
			$data = explode(",", $value);
			$r = array();

			foreach ($data as $value) {
				$link = JRoute::_("index.php?option=com_jajobboard&view=jajobs&layout=jalist&job_tags=" . $value);
				$r[] = "<a href='{$link}' title='{$value}' >{$value}</a>";
			}

			$r = implode(", ", $r);
		}

		// View permission
		if ($field->display == "show_all_registered" && getCurrentUserType() == "Guest") {
			$r = "<i style='color:red'>" . JText::_("YOU_MUST") . " <a target='_blank' href='" . JURI::root() . "index.php?option=com_users&view=login'>" . JText::_("LOGIN") . "</a> " . JText::_("TO_VIEW_THIS_INFORMATION") . "</i>";
		}
	} else if (!in_array($field->display, array('hide_on_new', 'hide_all')) && ($field->display != 'admin_only' || $isAdministrator)) {
		$readonly = $field->is_readonly ? 'readonly' : '';
		$disabled = $readonly && !$javascript ? 'disabled' : '';

		switch (trim($field->field_type)) {
			case 'BLANK':
				$r = '';
				break;

			case "BOOLEAN":
				if ($field->field_name == 'opt_attachment') {
					$mouse_event = 'onclick="check(this, \''.$order_item.'\');"';
				}

				$r = JHTML::_('select.booleanlist', $field->field_name, $class . ' ' . $mouse_event, $value);
				break;

			case "DATE":
/*				$r = "<input {$mouse_event} {$class} name='" . $field->field_name . "' id='" . $field->field_name . "' value='" . $value . "' $javascript>";*/
				$r = "<input {$mouse_event} {$class} name='" . $field->field_name . "' id='" . $field->field_name . "' value='" . $value . "' type='datepicker'>";
				break;

			case "DATE_CAL":
				$dateFormat = $jbconfig['general']->get('format_date');

				if ($dateFormat) {
					$dateFormat = explode(" ", $dateFormat);

					if (isset($dateFormat[1])) {
						$dateFormat = $dateFormat[0] . " " . str_replace(array("i", "s"), array("M", "S"), $dateFormat[1]);
					} else {
						$dateFormat = $dateFormat[0];
					}
				} else {
					$dateFormat = "%Y-%m-%d %H:%M:%S";
				}

				if ($value && is_string($value)) {
					$value = date(str_replace(array("%", "M", "S"), array("", "i", "s"), $dateFormat), strtotime($value));
				}

				$field->eventmouse = $field->eventmouse ? $field->eventmouse : 'onclick';
				$r = JHTML::_('calendar', $value, $field->field_name, $field->field_name, $dateFormat, array('class' => $className, 'size' => '25', 'maxlength' => '19', $field->eventmouse => $field->functionjs));
				break;

			case 'FILE':
				if (($view == 'jaapplications') && ($field->field_name == 'attachment') && ($item->opt_attachment)) {
					$input_disabled = 'disabled="disabled"';
				} else {
					$input_disabled = '';
				}

				if ($value) {
					$file_name = explode('/', $value);
					$r = '<span id="download_' . $field->field_name . '">' . "<a target='_blank' href='" . JRoute::_( 'index.php?option=' . JBCOMNAME . '&view=' . $view . '&task=download&field_name=' . $field->field_name . '&cid[]=' . $item->id) . "'>" . JText::_(substr($file_name[count($file_name) - 1], 14)) . "</a>" . '</span>&nbsp&nbsp';
					$r .= '<span id="remove_' . $field->field_name . '">(<a href ="javascript:void(0)" onclick="remove_attach(\'' . $field->field_name . '\',\'' . $item->id . '\')">' . JText::_('REMOVE') . '</a>)</span><br />';
				}

				$r .= "<input $input_disabled {$mouse_event} {$class} type='file' name='" . $field->field_name . "' id='" . $field->field_name . "' value='' $javascript /> ";
				$r .= "<br />" . "<i>" . JText::_("ALLOW_EXT") . ": " . $jbconfig['general']->get('allowed_ext') . ". " . JText::_('MAXIMUM_SIZE') . ": " . $jbconfig["general"]->get("max_upload_bytes") . " MB</i>";
				$r .= '<input type="hidden" name="text_' . $field->field_name . '" value="' . $value . '" id="text_' . $field->field_name . '" />';
				break;

			case 'IMAGE':

				if ($value) {
					$src = JURI::base() . $value;
					$temp_path = JPath::clean(JPATH_SITE . DS . $value);

					if (file_exists($temp_path)) {
						if (intval($field->field_width)) {
							//$size = "width='{$field->field_width}'";
							$size="";
							if (intval($field->field_height)) {
								$size .= " height='{$field->field_height}'";
							}
						} else {
							//$imgInfo = getimagesize($temp_path);
							$size = "";
						}

						$r = '<span id="download_' . $field->field_name . '">' . "<img src='" . $src . "' alt='' $size/>" . '</span><br />';
						$r .= '<span id="remove_' . $field->field_name . '">(<a href ="javascript:void(0)" onclick="remove_image(\'' . $field->field_name . '\',\'' . $item->id . '\')">' . JText::_('REMOVE') . '</a>)</span><br />';
					} else {
						$r = "<img src='" . JURI::base() . "/components/com_jajobboard/images/no-image.gif' alt='Default'/>";
					}
				} else {
					$r = "<img src='" . JURI::base() . "/components/com_jajobboard/images/no-image.gif' alt='Default'/>";
				}

				$r .= "<br/><input {$mouse_event} {$class} type='file' name='" . $field->field_name . "' id='" . $field->field_name . "' value=''/>";
				$r .= "<br />" . "<i>" . JText::_("ALLOW_EXT") . ": " . $jbconfig['general']->get('allowed_img') . ". " . JText::_('MAXIMUM_SIZE') . ": " . $jbconfig["general"]->get("max_upload_bytes") . " MB</i>";
				$r .= '<input type="hidden" name="text_' . $field->field_name . '" value="' . $value . '" id="text_' . $field->field_name . '" />';
				break;

			case 'URL':
			case 'TEXTFIELD':
			case 'NUMERIC':
			case 'INTEGER':
				/*$field->field_height = $field->field_height ? $field->field_height : 18;
				$field->field_width = $field->field_width ? $field->field_width : 150;*/

				$r = "<input {$mouse_event} {$class} type='text' name='" . $field->field_name . "' id='" . $field->field_name . "' $readonly value='" . $value . "' /> ";

				if ($field->field_name == "job_tags") {
					$r .= initialTags_JSVar();
				}
				break;

			case 'PASS':
				$field->field_height = $field->field_height ? $field->field_height : 18;
	//			$field->field_width = $field->field_width ? $field->field_width : 150;
				$r = "<input $mouse_event  $class type='password' name='" . $field->field_name . "' id='" . $field->field_name . "' $readonly value='' style=\"height:" . $field->field_height . "px;\" > ";
				break;
			case "EDITOR":

				// Load the JEditor object
				//$editor = JFactory::getEditor();

				$field->field_height = $field->field_height ? $field->field_height : 200;
				$field->field_width = $field->field_width ? $field->field_width : 300;

				// parameters : areaname, content, width, height, cols, rows, button?
/*				$r = $editor->display($field->field_name, $value, '100%', $field->field_height, '70', '15', false, $field->field_name, null, null, array('theme' => 'simple'));*/
$r='<textarea class="ckeditor" id="'.$field->field_name.'" name="'.$field->field_name.'"  rows="15" style="width: 100%; height: 200px; margin:0px; padding:0px">'.$value.'</textarea>';
				break;

			case 'MSELECT':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);


				if ($field->field_name != 'apply_type') {
					if (JRequest::getVar($field->field_name, "")) {
						$value = JRequest::getVar($field->field_name, "");
						$value = implode(",", $value);
					}

					$value = explode(',', $value);
					array_filter($value, 'trim');

					$select_options = array();
/*					$select_options[0]->value = '';
					$select_options[0]->text = sprintf(JText::_("PLEASE_SELECT_VAR"), rendfieldlabel($field));*/

					for ($i = 0; $i < count($arr_option); $i++) {
						$select_options[] = $arr_option[$i];
					}
					if(!count($select_options))
					{
						
						if(count($value))
						{
							foreach($value as $v)
							{
						$select_options[$v]=$v;		
							}
						}
						
					}

/*					$field->field_width = $field->field_width ? $field->field_width : 150;
					$field->field_height = $field->field_height ? $field->field_height : 200;*/
					$r = JHTML::_('select.genericlist', $select_options, $field->field_name . '[]', "{$mouse_event} {$class} size='4' $javascript multiple='multiple'", 'value', 'text', $value);
				} else {
					if ($value && !is_array($value)) {
						$value = explode(',', $value);
						array_filter($value, 'trim');
					}

					$enable_option = $jbconfig["posts"]->get('apply_option');

					if ($enable_option) {
						$enable_option = explode(',', $enable_option);
					} else {
						$enable_option = array('1', '2', '3');
					}

					$r = '';

					for ($i = count($arr_option) - 1; $i >= 0; $i--) {
						if (in_array($arr_option[$i]->value, $enable_option)) {
							$selected = '';
							$style = "none";

							if (!is_array($value)) {
								if (in_array(3, $enable_option)) {
									$value = array(3);
								} else {
									$value = array($enable_option[0]);
								}
							}

							if (in_array($arr_option[$i]->value, $value)) {
								$selected = 'checked="checked"';
								$style = "";
							}

							if ($arr_option[$i]->value == 1) {
								$r .= '<input ' . $selected . ' onclick="select_direct_url(this.checked)" type="checkbox" name="apply_type[]" id="apply_type' . $arr_option[$i]->value . '" value="' . $arr_option[$i]->value . '" /><label for="apply_type' . $arr_option[$i]->value . '">' . $arr_option[$i]->text . '</label>';
								$r .= ' <div id="s_direct_url" style="display:' . $style . ';vertical-align:top;"><br />';
								$direct_url_fields = getItemByFieldName('ja_form_fields', 'field_name', 'direct_url');

								for ($ii = 0; $ii < count($direct_url_fields); $ii++) {
									if ($direct_url_fields[$ii]->table_name == 'ja_jobs') {
										$direct_url_field = $direct_url_fields[$ii];
										$r .= rendfield($item, $direct_url_field, false, true);
										break;
									}
								}

								$r .= '</div>';
							} else {
								$r .= '<input ' . $selected . '  type="checkbox" name="apply_type[]" id="apply_type' . $arr_option[$i]->value . '" value="' . $arr_option[$i]->value . '" /><label for="apply_type' . $arr_option[$i]->value . '">' . $arr_option[$i]->text . '</label>';
							}

							if ($i > 0) {
								$r .= '<br />';
							}
						}
					}
				}
				break;

			case 'RADIO':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);
				if(is_array($value)) {
					$value = array_pop($value);
				}
				$r = JHTML::_('select.radiolist', $arr_option, $field->field_name, "{$mouse_event} {$class} {$javascript} {$disabled} size=\"1\" ", 'value', 'text', $value);
				break;

			case 'CHECK':
				if (!is_array($value)) {
					$value = explode(',', $value);
					array_filter($value, 'trim');
				}

				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);

				$r = '';

				for ($i = 0; $i < count($arr_option); $i++) {
					$selected = '';
					$style = "none";

					if (!is_array($value)) {
						$value = array();
					}

					if (in_array($arr_option[$i]->value, $value)) {
						$selected = 'checked="checked"';
						$style = "";
					}

					$r .= '<input ' . $selected . '  type="checkbox" name="' . $field->field_name . '[]" id="' . $field->field_name . '' . $arr_option[$i]->value . '" value="' . $arr_option[$i]->value . '" /><label for="' . $field->field_name . '' . $arr_option[$i]->value . '">' . $arr_option[$i]->text . '</label>';

					if ($i >= 0) {
						$r .= '<br />';
					}
				}
				break;

			case 'SELECT':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);


/*				$field->field_height = $field->field_height ? $field->field_height : 'auto';
				$field->field_width = $field->field_width ? $field->field_width : 'auto';*/
				$select_options = array();
				/*$select_options[0]->value = '';
				$select_options[0]->text = sprintf(JText::_("PLEASE_SELECT_S"), rendfieldlabel($field));*/

				for ($i = 0; $i < count($arr_option); $i++) {
					$select_options[] = $arr_option[$i];
				}

				$r = JHTML::_('select.genericlist', $select_options, $field->field_name, "{$mouse_event} {$class} $disabled size=\"1\" $javascript", 'value', 'text', $value);

				break;

			case 'CHECKBOXLIST':
				$arr_option = getFormFieldValues($field->table_name . '_' . $field->field_name);

				if ($arr_option) {
					foreach ($arr_option as $k => $op) {
						$check = '';
						settype($value, 'array');

						if (in_array($op->value, $value)) {
							$check = 'checked';
						}

						$r .= "<input {$mouse_event} {$class} type='checkbox'  name='$field->field_name[]' id='" . $field->field_name . $k . "' value='$op->value' $check><label for='{$field->field_name}{$k}'>" . $op->text . "</label><br/>";
					}
				}
				break;

			case 'TEXTAREA':
				$field->field_height = $field->field_height ? $field->field_height : 5;
/*				$field->field_width = $field->field_width ? $field->field_width : 60;*/
				$r = "<textarea $mouse_event  $class rows='$field->field_height' name='$field->field_name' id='$field->field_name'>$value</textarea>";
				break;

			case 'SEPERATOR':
				$r = rendfieldlabel($field);
				break;
			default:
				// Break
				break;
		}

		if (!$viewonly && !$field->is_readonly) {
			$r .= JText::_($comment);
		}

		if (($field->is_required == '1') && (!$viewonly) && (!$field->is_readonly) && !$javascript) {
			$r .= '<span style="color:red;" id="req-'.$field->field_name.'" class="ja-field-require">*</span>';
		}

		if (!$viewonly && $example != '') {
			$r .= '<br/>' . JText::_($example);
		}

	} else if ($field->display == 'hide_all') {
		$r = "<input {$mouse_event} {$class} type='hidden' name='" . $field->field_name . "' id='" . $field->field_name . "' value='" . htmlspecialchars($value) . "'> ";
	}

	return $r;
}
function rendfield_byname($item, $fieldlist, $field_name, $event = false, $edit = false, $viewonly = false, $view = null, $order_item = null)
{
rendfield($item, $field, $event, $edit, $viewonly, $view, $order_item);
}


function loadCufon()
{
if (!defined('JAJOBBOARD_CUFON_LOADED')) {
	JHTML::script('cufon.js', 'components/com_jajobboard/js/');
	JHTML::script('Collator_400.font.js', 'components/com_jajobboard/js/');
	
	define('JAJOBBOARD_CUFON_LOADED', 1);
}

}

/**
 * Get external combobox
 *
 * @param object  $field      Field object
 * @param string  $selected   Field is selected
 * @param boolean $edit     Is edit mode
 * @param int   $view     On view mode
 * @param string  $javascript   Embeded javascript string
 * @param int   $add_empty    Add empty value
 * @param string  $class      CSS class name
 * @param string  $mouse_event  Add mouse event
 *
 * @return string HTML string
 * */
function getExternalCombo($field, $selected = "", $edit = false, $view = false, $javascript = "", $add_empty = 1, $class = '', $mouse_event = '')
{
	$r = null;
	$user = JFactory::getUser();
	$db = JFactory::getDbo();
	$key = $field->referred_key;
	$value = $field->referred_value;
	$table = $field->referred_table;
	
	$isAdministrator = JBAccess::isInManagerGroup($user->id);

	if ($selected == "") {
		$selected = "0";
	}

	if (!$selected && $field->field_name == 'user_id') {
		$selected = $user->id;
	}

	if ((($edit && in_array($field->display, array('hide_on_edit', 'hide_on_new'))) || $view) && ($field->display != 'admin_only' || $isAdministrator) && $field->display != 'hide_all') {
		switch (trim($field->field_type)) {
			case 'MSELECT':
				if ($selected) {
					$query = "SELECT {$key}, {$value} FROM #__{$table} WHERE {$key} IN('".str_replace(",", "','", $selected)."')";
					$db->setQuery($query);
					$objects = $db->loadObjectList();

					$arr_value = array();
if(!$view)
{
$default='';
$default[$key]='';
$default[$value]='';
$arr_value[]=$defailt;
}
					for ($i = 0; $i < count($objects); $i++) {
						$arr_value[] = $objects[$i]->name;
					}

					$r = implode(', ', $arr_value);
				}

				if ($field->field_name == 'location_id') {
					$selected = explode(",", $selected);
					$r = "";

					foreach ($selected as $k => $str) {
						$str = trim($str);
						$result = show_full_location_tree($str);
						$temp = '';

						if (count($result) > 0) {
							$temp = implode(', ', $result);
						}

						if ($k > 0 && count($result) > 0) {
							$r .= "; " . str_replace(", ", " - ", $temp);
						} else {
							$r .= str_replace(", ", " - ", $temp);
						}
					}
				}
				break;

			default:
				if ($field->field_name != 'location_id') {
					$query = "SELECT $key, $value FROM #__$table WHERE $key = '$selected'";
					$db->setQuery($query);
					$object = $db->loadObject();

					if ($object) {
						$r = $object->$value;
					} else {
						$r = 'N/A';
					}
				} else {
					$result = show_full_location_tree($selected);
					$temp = '';
					if (count($result) > 0) {
						$temp = implode(', ', $result);
					}
					$r = $temp;
				}
				break;
		}

	} else if (!$edit && $field->display == 'hide_on_new') {
		$r = "<input {$mouse_event} {$class} type='hidden' name='" . $field->field_name . "' id='" . $field->field_name . "' $javascript value='" . $selected . "' /> ";
	} else if ($field->display == 'show_all' || ($field->display == 'admin_only' && $isAdministrator)) {
		$query = "SELECT * FROM #__$table where published = 1";

		//$isSpecialTables = ($field->referred_table == 'ja_categories' || $field->referred_table == 'ja_location') ? 1 : 0;
$isSpecialTables=0;
		if ($isSpecialTables) {
			$query = "SELECT * FROM #__{$table} where published = 1 ORDER BY  name ASC, id ASC ";
		}


		$objectHTML = array();

		if ($add_empty) {
/*			$objectHTML[] = JHTML::_('select.option', "", sprintf(JText::_("PLEASE_SELECT_VAR"), rendfieldlabel($field)));*/
		}

		if ($isSpecialTables) {
			if(trim($field->field_type) != 'MSELECT') {
				//if category and location list is displayed as multi select box
				//then they will be driven by javascript in getSelectList function, so do not need get its options here
				$objects = jaGetTreeItems($field->referred_table);
				for ($i = 0, $n = count($objects); $i < $n; $i++) {
					$objectHTML[] = JHTML::_('select.option', $objects[$i]->id, $objects[$i]->treename);
				}
			}
		} else {
			$db->setQuery($query);
			$objects = $db->loadObjectList();
			$default='';
				$objectHTML[] = JHTML::_('select.option', '', '');
			for ($i = 0, $n = count($objects); $i < $n; $i++) {
				$objectHTML[] = JHTML::_('select.option', $objects[$i]->{$key}, $objects[$i]->{$value});
			}
		}

//		$field->field_width = ($field->field_width > 150) ? $field->field_width : 150;

/*		if($add_empty && $field->field_width < 250) {
			$field->field_width = 250;
		}*/

		switch (trim($field->field_type)) {
			case 'MSELECT':
				if (!is_array($selected)) {
					if ($selected) {
						$selected = explode(',', $selected);
						foreach ($selected as $k=>$v){
							$selected[$k] = trim($v);
						}
					}
				}

				//$field->field_width = $field->field_width ? $field->field_width : 150;
				$field->field_height = $field->field_height ? $field->field_height : 200;

				if ($javascript) {
					$size = 1;
				} else {
					$size = 5;
				}



				//die($selectbox);
				//select box with auto completed
				if($isSpecialTables){
					$selectbox = getSelectList($field->referred_table, $field->field_name, $selected, '', $field->is_required);
				} else {
					$selectbox = JHTML::_('select.genericlist', $objectHTML, $field->field_name . '[]', " {$mouse_event} {$class} {$javascript} size='$size' multiple='multiple' style=\"height:{$field->field_height}px;\"", 'value', 'text', $selected);
				}
				$r .= $selectbox;


				break;

			case 'SELECT':
				$r = JHTML::_('select.genericlist', $objectHTML, $field->field_name, "{$mouse_event} {$class} {$javascript} ", 'value', 'text', $selected);
				break;

			default:
				$query = "SELECT $key, $value FROM #__$table WHERE $key = '$selected'";

				$db->setQuery($query);
				$object = $db->loadObject();
				$field->field_height = $field->field_height ? $field->field_height : 20;
//				$field->field_width = $field->field_width ? $field->field_width : 200;

				if (isset($object->$value)) {
					$r = "<input {$mouse_event} {$class} type='text' name='" . $field->field_name . "' id='" . $field->field_name . "' $javascript value='" . $object->$value . "' style=\"height:" . $field->field_height . "px;\" /> ";
				} else {
					$r = "<input {$mouse_event} {$class} type='text' name='" . $field->field_name . "' id='" . $field->field_name . "' $javascript value='' style=\"height:" . $field->field_height . "px;\" /> ";
				}
				break;
		}
	}

	return $r;
}

function getSelectList($table, $field_name, $selected_value = array(), $keyword = '', $required = false)
{
	global $jbconfig;
	$container = $field_name.'_container';

	if(empty($keyword)) {
		switch ($table) {
			case 'ja_categories': $keyword = JText::_("SEARCH_CATEGORY", true); break;
			case 'ja_location': $keyword = JText::_("SEARCH_LOCATION", true); break;
			default: $keyword = JText::_('SEARCH', true); break;
		}
	}

	//auto completed box
	$searchid = 'auto_'.$field_name;
	if(!defined('JB_AUTOCOMPLETE_SCRIPT')) {
		define('JB_AUTOCOMPLETE_SCRIPT', 1);
		JHTML::stylesheet('jquery.autocomplete.css', 'components/com_jajobboard/js/');
//		JHTML::script('jquery.js', 'components/com_jajobboard/js/');
		JHTML::script('jquery.autocomplete.js', 'components/com_jajobboard/js/');
	}
	$jsvar = '';
	$jsvar .= '<script type="text/javascript" language="javascript">';
	$jsvar .= '/* <![CDATA[ */';
	$jsvar .= "
    jQuery().ready(function(){
    	var sourceac_cat = '".JURI::root()."index.php?option=com_jajobboard&view=jajobalerts&task=getacdata&table={$table}&field_name={$field_name}';
        jQuery('#".$searchid."').autocomplete(sourceac_cat, {
            minChars: 2,
            width: 259,
            limit: 100,
            matchContains: 'word',
            autoFill: false,
            formatItem: function(row, i, max) {
                return  row.value;
            },
            formatMatch: function(row, i, max) {
                return row.id + ' ' + row.value;
            },
            formatResult: function(row) {
            	row.id = row[0];
            	row.value = row[1];
                return row;
            }
        }).result(function(event, data, formatted) {
        	var lbl_id			= 'lbl_'+data.id;
    		if(jQuery('input#'+data.id).length > 0){
    			alert('".JText::_('IT_IS_SELECTED', true)."');
			}else{
            	var newspan = document.createElement('span');
        		newspan.setAttribute('class','CL_".$field_name."_selected');
        		var contentnewspan 	= '';
        		contentnewspan		+=		data.value;
        		contentnewspan		+=		'<input id=\"'+data.id+'\" type=\"hidden\" name=\"".$field_name."[]\"  value=\"'+data.value+'\"  />' ;
        		contentnewspan		+=		'<img id=\"btn_'+data.id+'\" onclick=\"removeCL(this, \''+lbl_id+'\');\" alt=\"".JText::_('REMOVE')."\" title=\"".JText::_('REMOVE')."\" src=\"".JURI::root()."components/com_jajobboard/images/del.gif\" />' ;
        		newspan.innerHTML = contentnewspan ;
				jQuery('#".$container."').append(newspan);
                if(jQuery('label#'+lbl_id).length > 0){
                	jQuery('label#'+lbl_id).addClass('cl_selected');
                }
            }
            jQuery('input#".$searchid."').val('');
            jQuery('input#".$searchid."').focus();
        });
    });";

	$jsvar .= '/* ]]> */';
	$jsvar .= '</script>';

	$searchbox = '<input type="text" id="'.$searchid.'" class="autocomplete" value="'.$keyword.'" onblue="if (this.value == \'\') this.value=\'' .$keyword.'\';" onfocus="if (this.value == \''.$keyword .'\') this.value=\'\';" />';
	$searchbox .= $jsvar;
	//

	$r = '
	<div class="listbox">';
	if(!is_array($selected_value)){
		$selected_value = array($selected_value);
	}
	$cat_selected = '';
	if($jbconfig['general']->get('cl_ajax', 0) && $jbconfig['general']->get('cl_show_all_cat', 1)){
		$document = JFactory::getDocument();
		$r .= '<img src="components/com_jajobboard/images/loading.gif" width="16" height="16" />';

		$selected_value_data = count($selected_value) ? implode(',', $selected_value) : '';
		$selected_value_data = rawurlencode($selected_value_data);
		$document->addScriptDeclaration("
	    	window.addEvent('domready', function() {
	            var lo_ajax_url = 'index.php?option=com_jajobboard&view=jajobalerts&layout=load_cl_ajax&type={$table}&field_name={$field_name}&selected={$selected_value_data}';
	            var location_load_ajax = new Request({
	            	'url': lo_ajax_url,
	                method: 'post',
	                onComplete: function(response){
	                    $('".$container."').set('html', response);
	                }
	            }).send();
	        });");

		if(count($selected_value)){
			$categoryOptions = jaGetTreeItems($table);
			foreach ($categoryOptions as $key=>$value) {
				if (in_array($value->name, $selected_value) || in_array($value->id, $selected_value)) {
					$cat_selected .= '<span class="CL_'.$field_name.'_selected">';
					$cat_selected .=    $value->name ;
					$cat_selected .=    '<input type="hidden" value="'.$value->name.'" name="'.$field_name.'[]" id="'.$field_name.'_'.$value->id.'" />';
					$cat_selected .=    '<img id="btn_'.$field_name.'_'.$value->id.'" onclick="removeCL(this, \'lbl_'.$field_name.'_'.$value->id.'\')" src="'.JURI::root().'components/com_jajobboard/images/del.gif" title="'.JText::_('REMOVE').'" alt="'.JText::_('REMOVE').'" />';
					$cat_selected .= '</span>';
				}
			}
		}
	} else {
		$categoryOptions = jaGetTreeItems($table);
		foreach ($categoryOptions as $key=>$value) {
			$selected = 0;
			if (in_array($value->name, $selected_value) || in_array($value->id, $selected_value)) {
				$selected = 1;
				$cat_selected .= '<span class="CL_'.$field_name.'_selected">';
				$cat_selected .=    $value->name ;
				$cat_selected .=    '<input type="hidden" value="'.$value->name.'" name="'.$field_name.'[]" id="'.$field_name.'_'.$value->id.'" />';
				$cat_selected .=    '<img id="btn_'.$field_name.'_'.$value->id.'" onclick="removeCL(this, \'lbl_'.$field_name.'_'.$value->id.'\')" src="'.JURI::root().'components/com_jajobboard/images/del.gif" title="'.JText::_('REMOVE').'" alt="'.JText::_('REMOVE').'" />';
				$cat_selected .= '</span>';
			}
			if($jbconfig['general']->get('cl_show_all_cat', 1)){
				$r .= buildSelectListItem($field_name, $value, $selected);
			}
		}
	}
	$r .= '</div>';

	$box = '<div class="ja-select-list-wrapper">';
	$box .= '<div class="ja-select-list">'.$searchbox.$r.'</div>';
	$box .= '<div class="destbox-wrapper">';
	if($required) {
		$box .= '<span id="req-'.$field_name.'" class="ja-field-require" style="color:red; display:block; clear:both;">*</span>';
	}
	$box .= '<div class="destbox" id="'.$container.'">'.$cat_selected.'</div>';
	$box .= '</div>';
	$box .= '</div>';

	return $box;
}

function buildSelectListItem($field_name, $item, $active) {
	$container = $field_name.'_container';
	$active = $active ? ' class="cl_selected"' : '';
	$field = '<label '.$active.' id="lbl_'.$field_name.'_'.$item->id.'" onclick="selectCL(this,'.$item->id.', \''.$field_name.'\', \''.$item->name.'\', \''.$container.'\');">'.$item->treename.'</label><br />';
	return $field;
}


/**
     * Get send mail duration combobox
     *
     * @param string $selected_value Selected values
     *
     * @return string HTML selectbox string
     */
function getPostedDate($selected_value='')
{
	$htmlOptions = array();

	$htmlOptions[] = JHTML::_('select.option', '', JText::_('ALL'));
	$htmlOptions[] = JHTML::_('select.option', '1', JText::sprintf('ONEDAY_NUMBER', 1));
	
	for($i=2;$i<=14;$i++)
	$htmlOptions[] = JHTML::_('select.option', $i, JText::sprintf('DAYS_NUMBER', $i));
	$htmlOptions[] = JHTML::_('select.option', '30', JText::sprintf('DAYS_NUMBER', 30));
	$htmlPostedDate = JHTML::_('select.genericlist', $htmlOptions, 'posted_date', '', 'value', 'text', $selected_value);

	return $htmlPostedDate;
}

/**
     * Get Job Distance combobox
     *
     * @param string $selected_value Selected values
     *
     * @return string HTML selectbox string
     */
function getJobDistance($selected_value, $field_name)
{
	$htmlOptions = array();
	$htmlOptions[] = JHTML::_('select.option', '', JText::_('ALL'));
	$result = getFormFieldValues('ja_jobs_distance');
	if (is_array($result)) {
		for ($i=0;$i<count($result);$i++) {
			$htmlOptions[] = JHTML::_('select.option', $result[$i]->value, $result[$i]->text);
		}
	}
	$htmlJobType = JHTML::_('select.genericlist', $htmlOptions, 'job_distance[]', ' multiple', 'value', 'text', $selected_value);

	return $htmlJobType;
}

/**
     * Get Job type combobox
     *
     * @param string $selected_value Selected values
     *
     * @return string HTML selectbox string
     */
function getJobType($selected_value)
{
	$htmlOptions = array();
	$htmlOptions[] = JHTML::_('select.option', '', JText::_('ALL'));
	$result = getFormFieldValues('ja_jobs_job_type');
	if (is_array($result)) {
		for ($i=0;$i<count($result);$i++) {
			$htmlOptions[] = JHTML::_('select.option', $result[$i]->value, $result[$i]->text);
		}
	}
	$htmlJobType = JHTML::_('select.genericlist', $htmlOptions, 'job_type', '', 'value', 'text', $selected_value);
	return $htmlJobType;
}

/**
 * Generate javascript for select tags
 *
 * @return string HTML string
 * */
function prepare_url($url)
{
if(empty($url))
return '#';

	$url_data=explode('http',$url);
	if(count($url_data)>1)
	return $url;
	else
	return 'http://'.$url;
	

}
function getMenuLink($url)
{
$app = JFactory::getApplication();
$menu = $app->getMenu();
$menuItem = $menu->getItems( 'link', $url, true );
if($menuItem)
{
$Itemid = $menuItem->id;
return $url.'&amp;Itemid='.$Itemid;
}
return $url;
}

function readable_date($date_string,$ignore_current_year=false)
{
if($date_string=='0000-00-00 00:00:00')
return false;
$months=array(
1=>'Janvier',
2=>'Février',
3=>'Mars',
4=>'Avril',
5=>'Mai',
6=>'Juin',
7=>'Juillet',
8=>'Août',
9=>'Septembre',
10=>'Octobre',
11=>'Novembre',
12=>'Décembre'
);
$date = new DateTime($date_string);
	$d=(int)$date->format('d');
	$m=$date->format('m');
	$m=$months[(int)$m];
	
	$y=$date->format('Y');
if($ignore_current_year)
{
	if($y==date('Y'))
	$y='';
}
	return $d.' '.$m.' '.$y;
}
function initialTags_JSVar()
{
	$db = JFactory::getDbo();
	$db->setQuery("SELECT id, title FROM #__ja_tags WHERE published=1 ORDER BY title ASC");
	$arrTags = $db->loadObjectList();

	$jsvar = '';
	$jsvar .= '<script type="text/javascript" src="' . JURI::root() . 'components/com_jajobboard/js/jquery.js" language="javascript"></script>';
	$jsvar .= '<script type="text/javascript" src="' . JURI::root() . 'components/com_jajobboard/js/jquery.autocomplete.js" language="javascript"></script>';
	$jsvar .= '<link rel="stylesheet" type="text/css" href="' . JURI::root() . 'components/com_jajobboard/js/jquery.autocomplete.css" />';
	$jsvar .= '<script type="text/javascript" language="javascript">';
	$jsvar .= '/* <![CDATA[ */';
	$jsvar .= 'child_tag = new Array();';

	foreach ($arrTags as $key => $value) {
		$jsvar .= 'child_tag[' . $value->id . ']=new Array();';
	}

	$jsvar .= "var data_tags = [";
	$i = 0;

	foreach ($arrTags as $key => $value) {
		if ($i < count($arrTags) - 1) {
			$jsvar .= "'" . $value->title . "',";
		} else {
			$jsvar .= "'" . $value->title . "'";
		}
		$i++;
	}
	$jsvar .= "];

    jQuery().ready(function() {
      jQuery('#job_tags').autocomplete(data_tags, {
        multiple: true,
        mustMatch: false,
        autoFill: true
      });
    });";

	$jsvar .= '/* ]]> */';
	$jsvar .= '</script>';
}
function table_field($field_name,$table_name,$selected=array(),$default='',$type='select',$multiple=false)
{
if(!is_array($selected))
$selected=explode(',',$selected);
$db=JFactory::getDbo();
$db->setQuery("SELECT id,name FROM #__$table_name order by name asc");
$cat_list=$db->loadObjectList();
if($type=='select'){
?>
<select <?php if($multiple) echo 'multiple="multiple"' ?> id="<?php echo $field_name ?>" name="<?php echo $field_name ?><?php if($multiple) echo '[]' ?>">
<option value=""><?php echo $default ?></option>
<?php
foreach($cat_list as $cat)
{
?>
<option <?php if(in_array($cat->id,$selected)) echo 'selected';?> value="<?php echo $cat->id ?>"><?php echo $cat->name ?></option>
<?php
}
 ?>
</select>
<?php
}
else
{
foreach($cat_list as $cat)
{
?>
<input <?php if(in_array($cat->id,$selected)) echo 'checked';?> id="<?php echo $field_name ?>_<?php echo $cat->id ?>" type="checkbox" name="<?php echo $field_name ?>[]" value="<?php echo $cat->id ?>" />
<label for="<?php echo $field_name ?>_<?php echo $cat->id ?>"><?php echo $cat->name ?></label>
<br />
<?php
}
}
}



function salary_range($field_min_name='salary_min',$field_max_name='',$field_min_value='0',$field_max_value='150000')
{
if(!$field_min_value)
$field_min_value='0';
if(!$field_max_value)
$field_max_value='150000';

$id=uniqid('range_');
?>
<div id="field_<?php echo $id ?>">
<div style="height:13px;" class="salary_field">
<div id="<?php echo $id ?>"></div>
</div>
<p class="salary_label" style="text-align:center; font-weight:bold">
<?php if($field_min_value==0 && $field_max_value==150000)
{
echo JText::_('ALL_SALARY');  
}
else
{
if($field_max_name)	
echo 'entre '.$field_min_value.' &euro; et '.$field_max_value.' &euro;';
else
echo $field_min_value;
}
		   ?>
          </p>
<input class="range_from" type="hidden" name="<?php echo $field_min_name?>" value="<?php echo $field_min_value ?>" />
<?php if($field_max_name){?>
<input class="range_to" type="hidden" name="<?php echo $field_max_name?>" value="<?php echo $field_max_value ?>" />
<?php
}
?>
</div>
<style>
/*range slider*/
.salary_field > div
{
/*	height:13px !important;*/
	overflow:visible !important;
}
#<?php echo $id ?>
{
	box-shadow:inset 0px 5px 5px rgba(0,0,0,.3);
	-webkit-box-shadow:inset 0px 5px 5px rgba(0,0,0,.3);
	-moz-box-shadow:inset 0px 5px 5px rgba(0,0,0,.3);
}
#<?php echo $id ?>.ui-slider
{
	margin:0px 5px !important;
	overflow:visible !important;
}
#<?php echo $id ?>.ui-slider .ui-slider-handle
{
/*	width:22px !important;*/
	cursor:pointer;
	background:none;
	border:none;
	margin-top:1px;
/*	background:url(<?php echo JURI::root()?>/components/com_jajobboard/images/cursors.png) right bottom no-repeat;*/
	box-shadow:0px 0px 9px 1px rgba(185,2,89,.9);
	-webkit-box-shadow:0px 0px 9px 1px rgba(185,2,89,.9);
	-moz-box-shadow:0px 0px 9px 1px rgba(185,2,89,.9);
	
background: #f9bbf9;
background: -moz-linear-gradient(top,  #f9bbf9 0%, #ff3fc5 93%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f9bbf9), color-stop(93%,#ff3fc5));
background: -webkit-linear-gradient(top,  #f9bbf9 0%,#ff3fc5 93%);
background: -o-linear-gradient(top,  #f9bbf9 0%,#ff3fc5 93%);
background: -ms-linear-gradient(top,  #f9bbf9 0%,#ff3fc5 93%);
background: linear-gradient(to bottom,  #f9bbf9 0%,#ff3fc5 93%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9bbf9', endColorstr='#ff3fc5',GradientType=0 );



}
#<?php echo $id ?>.ui-slider .ui-widget-header
{
	background:#389fd4;
}
</style>
<script>
function prepare_slider_<?php echo $id ?>()
{
	var slider_min=0;
	var slider_max=150000;
	jQuery( "#<?php echo $id ?>" ).slider({
      range: true,
      min: slider_min,
      max: slider_max,
	  <?php if($field_max_name) {?>
      values: [ <?php echo $field_min_value ?>, <?php echo $field_max_value ?> ],
	  <?php }else { ?>
	  values: '<?php echo $field_min_value ?>',
	  <?php } ?>
	  animate: "slow" ,
	  step:5000,
      slide: function( event, ui ) {
//		  var from=parseInt(parseInt(ui.values[ 0 ])/1000);
//		  from='€ '+((from>50)?from+'K':ui.values[ 0 ]);
		  var from='€ '+parseInt(parseInt(ui.values[ 0 ]));
//		  var to=parseInt(parseInt(ui.values[ 1 ])/1000);
//		  to='€ '+((to>50)?to+'K':ui.values[ 1 ]);
		  var to='€ '+parseInt(parseInt(ui.values[ 1 ]));

		  var visible_salary=(parseInt(ui.values[ 1 ])+parseInt(ui.values[ 0 ]))/2;
		  
	jQuery('#visible_salary').val(visible_salary);
	  
	jQuery('#field_<?php echo $id ?> .salary_label').text(from+' - '+to);
    jQuery("#field_<?php echo $id ?> .range_from" ).val( ui.values[ 0 ] );
    jQuery("#field_<?php echo $id ?> .range_to" ).val( ui.values[ 1 ] );
	  
      }
    });
}
jQuery(document).ready(function(e) {
	prepare_slider_<?php echo $id ?>();
    jQuery('#<?php echo $id ?>.ui-slider a:first').addClass('left-handler');
});

</script>
<?php
}

function metro_field($field_name,$selected=array())
{
if(!is_array($selected))
$selected=explode(',',$selected);
$db=JFactory::getDbo();
$db->setQuery("SELECT * FROM #__ja_metros where type='metro'");
$metro_list=$db->loadObjectList();
$db->setQuery("SELECT * FROM #__ja_metros where type='rer'");
$rer_list=$db->loadObjectList();
$db->setQuery("SELECT * FROM #__ja_metros where type='transilien'");
$transilien_list = $db->loadObjectList();
$id=uniqid('select_metro_');
?>
<div id="<?php echo $id ?>">
      <ul class="select_metro">
        <?php 
foreach($rer_list as $m) {
$checked=(in_array($m->id,$selected))?'checked="checked"':'';
?>
        <li class="icon_<?php echo $m->id?>">
          <input style="display:none" type="checkbox" name="<?php echo $field_name ?>[]" value="<?php echo $m->id ?>" <?php echo $checked ?> />
          <a href="#" <?php if($checked!='') echo 'class="active"' ?>></a>
          <div class="mytooltip"><div class="content"><span class="title tip_<?php echo $m->id?>"><?php echo $m->name ?></span><p class="description desc_<?php echo $m->id?>"><?php echo $m->description ?></p></div>
          <div class="arrow down"></div></div>
        </li>
        <?php } ?>
<li><span class="actions"><a class="all" href="#"><?php echo JText::_('ALL') ?></a> / <a class="none" href="#"><?php echo JText::_('JNONE') ?></a></span></li>
</ul>
      <ul class="select_metro">
<?php
foreach($transilien_list as $m) {
$checked=(in_array($m->id,$selected))?'checked="checked"':'';	
?>
        <li class="icon_<?php echo $m->id?>">
                   <input style="display:none" type="checkbox" name="<?php echo $field_name ?>[]" value="<?php echo $m->id ?>" <?php echo $checked ?> />
          <a href="#" <?php if($checked!='') echo 'class="active"' ?>></a>
          <div class="mytooltip up"><div class="content"><span class="title tip_<?php echo $m->id?>"><?php echo $m->name ?></span><p class="description desc_<?php echo $m->id?>"><?php echo $m->description ?></p></div>
          <div class="arrow"></div></div>
        </li>
        <?php } ?>
        <li><span class="actions"><a class="all" href="#"><?php echo JText::_('ALL') ?></a> / <a class="none" href="#"><?php echo JText::_('JNONE') ?></a></span></li>
      </ul>
      <ul class="select_metro">
        <?php 
foreach($metro_list as $m) {
$checked=(in_array($m->id,$selected))?'checked="checked"':'';
?>
        <li class="icon_<?php echo $m->id?>">
                    <input style="display:none" type="checkbox" name="<?php echo $field_name ?>[]" value="<?php echo $m->id ?>" <?php echo $checked ?> />
          <a href="#" <?php if($checked!='') echo 'class="active"' ?>></a>
          <div class="mytooltip">
          <div class="content"><span class="title tip_<?php echo $m->id?>"><?php echo $m->name ?></span><p class="description desc_<?php echo $m->id?>"><?php echo $m->description ?></p></div>
          <div class="arrow"></div>
          </div>
        </li>
        <?php } 

?>
<li><span class="actions"><a class="all" href="#"><?php echo JText::_('ALL') ?></a> / <a class="none" href="#"><?php echo JText::_('JNONE') ?></a></span></li>
      </ul>
</div>
<script>
jQuery(document).ready(function(e) {

//prepare metro selector
jQuery('#<?php echo $id ?> ul.select_metro > li > a').click(function(){
var icon=jQuery(this);
var li_parent=icon.parent();
if(icon.hasClass('active'))
{
icon.removeClass('active');
li_parent.find('input').prop('checked',false);
}
else
{
icon.addClass('active');
li_parent.find('input').prop('checked',true);
}
return false;
});

jQuery('#<?php echo $id ?> ul.select_metro > li span.actions a.all').click(function(){
jQuery(this).parents('ul:first').find('li > a').each(function(){
jQuery(this).addClass('active').parent().find('input').prop('checked',true);
});
return false;
});

jQuery('#<?php echo $id ?> ul.select_metro > li span.actions a.none').click(function(){
jQuery(this).parents('ul:first').find('li > a').each(function(){
jQuery(this).removeClass('active').parent().find('input').prop('checked',false);
});
return false;
});

});
</script>
	<?php

}
function display_metro_field($metro_list,$direction='up')
{

if(!$metro_list)
return false;
if(!is_array($metro_list))
{
$db=JFactory::getDbo();
$q="select * from #__ja_metros where id in($metro_list)";
$db->setQuery($q);
$metro_list=$db->loadObjectList();
}

?>
<ul class="select_metro">
<?php
if(!empty($metro_list))
foreach($metro_list as $m) {
?><li class="icon_<?php echo $m->id?>"><a href="#"></a><div class="mytooltip <?php echo $direction ?>">
<?php if($direction=='down') { ?>
<div class="arrow"></div>
<?php } ?>
<div class="content"><span class="title tip_<?php echo $m->id?>"><?php echo $m->name ?></span><p class="description desc_<?php echo $m->id?>"><?php echo $m->description ?></p></div>
<?php if($direction=='up') { ?>
<div class="arrow"></div>
<?php } ?>
          </div>
        </li>
        <?php } ?>
</ul>
<?php
}
function display_tags($tags)
{
?>
<div class="tags">
<?php if(count($tags)){ ?>
<span class="tag">
<?php }

if(is_array($tags))
echo implode('</span><span class="tag">',$tags);
else
echo str_replace(',','</span><span class="tag">',$tags);

if(count($tags)){ 
?></span>
<?php }?>

</div>
<?php
}
function display_fonction_cible($selected)
{

}
/**
 * Generate javascript for field
 *
 * @return void
 * */
function rendfield_JS()
{
?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
urlroot = '<?php echo JURI::base(); ?>';
<?php
$view = JRequest::getCmd('view');
$layout = JRequest::getCmd('layout');
if (($view == 'jajobs') && ($layout == 'jaview')) {
	$view = 'jaapplications';
}
?>
var view = '<?php echo $view; ?>';
function remove_attach(field_name,item_id)
{
	if (!confirm('<?php echo JText::_('DO_YOU_WANT_TO_REMOVE_ATTACH_FILE', TRUE); ?>')) {
		return;
	}

	var url = urlroot + "index.php?option=com_jajobboard&view=<?php echo $view; ?>&task=remove_attach&field_name="+field_name+"&item_id="+item_id;
	var req = new Request({
		url: url,
		method:'get',
		data:  { 'text'  : field_name  },
		onComplete:updateRemoveAttach
	}).send();
}

function updateRemoveAttach(text)
{
	if (text!='')
	{
		
		jQuery('download_'+text).html('');
		jQuery('text_'+text).val('');
		jQuery('remove_'+text).html('');
		jQuery('.txt_'+text).remove();

		if (view=='jaapplications') {
			if (document.getElementById('opt_attachment0')) {
				document.getElementById('opt_attachment0').checked = true;
			}

			document.getElementById(text).disabled = false;
		}
	}
}

function remove_image(field_name,item_id)
{
	if (!confirm('<?php echo JText::_('DO_YOU_WANT_TO_REMOVE_IMAGE', TRUE); ?>')) {
		return;
	}

	var url = "index.php?option=com_jajobboard&view=<?php echo $view; ?>&task=remove_attach&field_name="+field_name+"&item_id="+item_id;
	new Request({
		url: url,
		method:'get',
		data:  { 'text'  : field_name  },
		onComplete:updateRemoveImage
	}).send();
}

function updateRemoveImage(text)
{
	if (text!='') {
		document.getElementById('download_'+text).innerHTML = "<img src='<?php echo JURI::base(); ?>/components/com_jajobboard/images/no-image.gif' alt='Default'/>";
		document.getElementById('text_'+text).value = '';
		document.getElementById('remove_'+text).innerHTML = '';

		if (view=='jaapplications') {
			document.getElementById('opt_attachment0').checked = true;
			document.getElementById(text).disabled = false;
		}
	}
}

function update_selected_alert(type)
{
if(type=='job')
var url = urlroot + "/index.php?option=com_jajobboard&view=jajobalerts&task=update_alert&tmpl=component";
else
var url = urlroot + "/index.php?option=com_jajobboard&view=jaresumealert&task=update_alert&tmpl=component";

jQuery.post(url, jQuery('#update_alert').serialize());
update_dashboard_alert()
}

function update_dashboard_alert()
{
var mail_mode=jQuery('#update_alert input[name=mode]:checked').val();
var send_mail_duration=jQuery('#sendmail_duration').val();
jQuery('#update_alert .check').each(function(){
if(this.checked)
{
var row=jQuery(this).parents('tr:first');
var sendmail_col=row.find('.sendmail');
var typemail_col=row.find('.mode');
console.log(mail_mode);
if(send_mail_duration=='0')
sendmail_col.removeClass('enabled').addClass('disabled');
else
sendmail_col.removeClass('disabled').addClass('enabled');

if(mail_mode=='1')
typemail_col.text('Html');
else
typemail_col.text('Text');

typemail_col.removeClass('mode_1 mode_0').addClass('mode_'+mail_mode);

}
});
}
/* ]]> */
</script>
<?php
}
?>
