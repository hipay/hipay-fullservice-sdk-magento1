<?php 
class Allopass_Hipay_Block_Adminhtml_System_Config_Form_Field_MultiselectSortable extends  Mage_Adminhtml_Block_System_Config_Form_Field
{
	
	/**
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		 $javaScript = "
            <script type=\"text/javascript\">
		 		var options = $$(\"#row_".$element->getHtmlId()." ul.checkboxes li\");
		 		options.each(function(e){
		 				var input = e.select('input').first();
		 				input.name = input.name + '[]';
		 				
		 				var label = e.select('label').first();
		 				label.setStyle({cursor:'move'});
		 				label.writeAttribute('for',false);
	
				});
		 		/*$$(\"#row_".$element->getHtmlId()." ul.checkboxes li input\").each(function(e,i)
		 				{
		 				   e.name = e.name + '[]';
		 				});
		 				
		 		$$(\"#row_".$element->getHtmlId()." ul.checkboxes li label\").each(function(e,i)
		 				{
		 				   e.setStyle({cursor:'move'});
		 				   e.writeAttribute({for}:'');
		 				});*/
		 				
		 		
            	//Sortable.create('".$element->getHtmlId()."',{elements:$$('#".$element->getHtmlId()." option'),handles:$$('#".$element->getHtmlId()." option')});
            	var container = $$(\"#row_".$element->getHtmlId()." ul.checkboxes\").first();
            	Sortable.create(container);
            </script>";
		$element->setData('after_element_html',$javaScript.$element->getAfterElementHtml());
		
		return parent::_getElementHtml($element);
	}
	
	
}