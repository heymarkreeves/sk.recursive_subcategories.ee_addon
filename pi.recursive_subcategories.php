<?php

// Copyright(c) 2005 Maurice Fäh

/**
 * For more information read the introduction towards the end of this file.
 **/

/*
* Change log:
* Beta 1: Proof of concept. Single tag outputs nested UL of subcategories based on root node ID. Optionally shows root node as first UL/LI.
* Beta 1.1:
	Supports category_id, category_name, category_url_tile and category_description tags
	Supports sort_by (default = cat_order), options = name, id, order
	Supports sort_direction (default = asc), options = asc, desc
	Supports backspace
	Supports style (default = linear), options = linear, nested
*/
 
// ----------------------------------------
//  Plugin information array
// ----------------------------------------

$plugin_info = array(
						'pi_name'			=> 'Recursive Subcategories',
						'pi_version'		=> 'Beta 1.1',
						'pi_author'			=> 'Mark J. Reeves - Slim Kiwi',
						'pi_author_url'		=> 'http://www.slimkiwi.com/',
						'pi_description'	=> 'Show a recursive list of subcategories for a given category. Optionally show the root node.',
						'pi_usage'			=> Recursive_Subcategories::usage()
					);
    
// ----------------------------------------
//  Plugin class
// ----------------------------------------

class recursive_subcategories {
	
	// ----------------------------------------
	//  mandatory plugin attributes
	// ----------------------------------------
	
	var $return_data = '';
	
	// ----------------------------------------
	//  plugin parameter attributes
	// ----------------------------------------
	
	var $parent_id = 0;
	var $show_parent = 0;
	var $sort_by = ' ORDER BY cat_order ';
	var $sort_direction = ' ASC ';
	var $backspace = 0;
	var $style = 'nested';

	// ----------------------------------------
	//  various plugin attributes
	// ----------------------------------------
	
	// ----------------------------------------
	//  class constructor
	// ----------------------------------------
	
	function recursive_subcategories()
	{	//return subcategories
		$this->get_nested_kickoff($this->parent_id, $this->show_parent);
		$this->return_data = substr($this->return_data, 0, strlen($this->return_data)-$this->backspace);
		return $this->return_data;
	}
	
	// ----------------------------------------
	//  plugin 3rd segment functions
	// ----------------------------------------
	
	function test()
	{		//return subcategories
	}
	
	// ----------------------------------------
	//  setter and getter functions
	//	encapsulation for class attributes
	// ----------------------------------------
	
	function set_return_data()
	{		//encapsulation of return_data attribute
	}

	function get_return_data()
	{		//encapsulation of return_data attribute
	}
	
	// ----------------------------------------
	
	function get_nested_kickoff()
	{
		global $TMPL;

		if ($TMPL->fetch_param('parent_id') != '')
		{
			$this->parent_id = $TMPL->fetch_param('parent_id');
		}
		else
		{
			$this->parent_id = 1;	//	Default setting
		}

		if ($TMPL->fetch_param('show_parent') != '')
		{
			$this->show_parent = $TMPL->fetch_param('show_parent');
		}
		else
		{
			$this->show_parent = $this->show_parent;	//	Default setting
		}
		
		if ($TMPL->fetch_param('sort_by') != '')
		{
			if ($TMPL->fetch_param('sort_by') == 'name')
			{
				$this->sort_by = " ORDER BY cat_name ";
			}
			elseif ($TMPL->fetch_param('sort_by') == 'id')
			{
				$this->sort_by = " ORDER BY cat_id ";
			}
			elseif ($TMPL->fetch_param('sort_by') == 'order')
			{
				$this->sort_by = " ORDER BY cat_order ";
			}
		}
		else
		{
			$this->sort_by = $this->sort_by;	//	Default setting
		}
		
		if ($TMPL->fetch_param('sort_direction') != '')
		{
			if ($TMPL->fetch_param('sort_direction') == 'asc')
			{
				$this->sort_direction = " ASC ";
			}
			elseif ($TMPL->fetch_param('sort_direction') == 'desc')
			{
				$this->sort_direction = " DESC ";
			}
		}
		else
		{
			$this->sort_direction = $this->sort_direction;	//	Default setting
		}
		
		if ($TMPL->fetch_param('backspace') != 0)
			$this->backspace = $TMPL->fetch_param('backspace');
		
		if ($TMPL->fetch_param('style') != '')
		{
			$this->style = $TMPL->fetch_param('style');
		}
		else
		{
			$this->style = $this->style;	//	Default setting
		}

		//$this->return_data = $this->get_nested(29,1);
		$this->get_nested($this->parent_id,$this->show_parent);
		//this->return_data = 'ul';
	}

	function get_nested($parent_id, $show_cat=0)
	{
		$str_output;
		global $DB, $TMPL, $FNS;
		
    	$tagdata = $TMPL->tagdata;
		$tagdata_top = '';

		if ($show_cat==1)
		{
			$query = $DB->query("Select cat_id, cat_name, cat_url_title, cat_description from exp_categories where cat_id = $parent_id");
			foreach($query->result as $row)
			{
				$tagdata_top = $TMPL->tagdata;
				
				// ----------------------------------------
				//   parse single variables
				// ----------------------------------------
				foreach ($TMPL->var_single as $key => $val)
				{
					//parse category_name variable
					if ($key == 'category_name')
					{
						if (isset($row['cat_name']))
						{
							$tagdata_top = $TMPL->swap_var_single($val, $row['cat_name'], $tagdata_top);
						}
					}
					//parse category_id variable
					if ($key == 'category_id')
					{
						if (isset($row['cat_id']))
						{
							$tagdata_top = $TMPL->swap_var_single($val, $row['cat_id'], $tagdata_top);
						}
					}
					//parse category_url_title variable
					if ($key == 'category_url_title')
					{
						if (isset($row['cat_url_title']))
						{
							$tagdata_top = $TMPL->swap_var_single($val, $row['cat_url_title'], $tagdata_top);
						}
					}
					//parse category_description variable
					if ($key == 'category_description')
					{
						if (isset($row['cat_description']))
						{
							$tagdata_top = $TMPL->swap_var_single($val, $row['cat_description'], $tagdata_top);
						}
					}
				}
			}
			if ($this->style == 'nested')
				$tagdata_top = '<ul><li>' . $tagdata_top;
		}

		$query = $DB->query("Select cat_id, cat_name from exp_categories where parent_id = $parent_id" . $this->sort_by . $this->sort_direction);
		
		if ($query->num_rows > 0)
		{
			if ($this->style == 'nested')
				$this->return_data .= '<ul>';
			foreach($query->result as $row)
			{
				$tagdata = $TMPL->tagdata;
				
				// ----------------------------------------
				//   parse single variables
				// ----------------------------------------
				foreach ($TMPL->var_single as $key => $val)
				{
					//parse category_name variable
					if ($key == 'category_name')
					{
						if (isset($row['cat_name']))
						{
							if ($this->style == 'nested')
								$tagdata = '<li>' . $TMPL->swap_var_single($val, $row['cat_name'], $tagdata) . '</li>';
							else
								$tagdata = $TMPL->swap_var_single($val, $row['cat_name'], $tagdata);
							//$tagdata = str_replace("&#47;", "/", $tagdata);
						}
					}
					//parse category_id variable
					if ($key == 'category_id')
					{
						if (isset($row['cat_id']))
						{
							if ($this->style == 'nested')
								$tagdata = '<li>' . $TMPL->swap_var_single($val, $row['cat_id'], $tagdata) . '</li>';
							else
								$tagdata = $TMPL->swap_var_single($val, $row['cat_id'], $tagdata);
							//$tagdata = str_replace("&#47;", "/", $tagdata);
						}
					}
					//parse category_url_title variable
					if ($key == 'category_url_title')
					{
						if (isset($row['cat_url_title']))
						{
							if ($this->style == 'nested')
								$tagdata = '<li>' . $TMPL->swap_var_single($val, $row['cat_url_title'], $tagdata) . '</li>';
							else
								$tagdata = $TMPL->swap_var_single($val, $row['cat_url_title'], $tagdata);
							//$tagdata = str_replace("&#47;", "/", $tagdata);
						}
					}
					//parse category_description variable
					if ($key == 'category_description')
					{
						if (isset($row['cat_description']))
						{
							if ($this->style == 'nested')
								$tagdata = '<li>' . $TMPL->swap_var_single($val, $row['cat_description'], $tagdata) . '</li>';
							else
								$tagdata = $TMPL->swap_var_single($val, $row['cat_description'], $tagdata);
							//$tagdata = str_replace("&#47;", "/", $tagdata);
						}
					}
				}
				$this->return_data .= $tagdata;
				$parent_id = $row['cat_id'] + 0;
				$tagdata .= $this->get_nested($parent_id);
			}
			if ($this->style == 'nested')
				$this->return_data .= '</ul>';
		}
		
		$this->return_data = $tagdata_top . $this->return_data;
		
		if ($show_cat==1)
		{
			if ($this->style == 'nested')
				$this->return_data .= '</li></ul>';
		}

		/*if ($query->num_rows > 0)
		{
		$str_output .= '<ul>';
		foreach($query->result as $row)
		{
			$str_output .= '<li>' . $row['cat_name'];
			$parent_id = $row['cat_id'] + 0;
			$str_output .= $this->get_nested($parent_id);
			$str_output .= "</li>\n";
		}
		$str_output .= '</ul>';
		}

		if ($show_cat==1)
		{
			$str_output .= '</li></ul>';
		}
		
		return $str_output;*/
	}
	

	// ----------------------------------------
	//  Plugin Usage
	// ----------------------------------------

	function usage()
	{
		ob_start(); 
?>
Current usage:
{exp:recursive_subcategories parent_id="29" show_parent="1"}{category_name}{/exp:recursive_subcategories}
Supports category_id, category_name, category_url_tile and category_description tags
Supports sort_by (default = cat_order), options = name, id, order
Supports sort_direction (default = asc), options = asc, desc
Supports backspace
Supports style (default = linear), options = linear, nested

<?php
	$buffer = ob_get_contents();
	
	ob_end_clean(); 

	return $buffer;
	}
// END


}
// END CLASS
?>