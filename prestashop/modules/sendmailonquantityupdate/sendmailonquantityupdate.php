<?php   
if (!defined('_PS_VERSION_'))
  exit;

class SendMailOnQuantityUpdate extends Module
{
	public function __construct()
	{
		$this->name = 'sendmailonquantityupdate';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'Guillaume Bolano';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;
		$this->old_quantity = null;

		parent::__construct();

		$this->displayName = $this->l('Send Mail On Quantity Update');
		$this->description = $this->l('Send mail when quantity of product is modified through the backoffice');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if(!Configuration::get('MYMODULE_NAME'))
			$this->warning = $this->l('No name provided');
	}

	public function install()
	{
		// Check that the Multistore feature is enabled, and if so, set the current context to all shops on this installation of PrestaShop.
		// Check that the module parent class is installed.
		// Check that the module can be attached to the leftColumn hook.
		// Check that the module can be attached to the header hook.
		// Create the MYMODULE_NAME configuration setting, setting its value to "my friend".
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		if (!parent::install() ||
			!$this->registerHook('actionProductUpdate') ||
			!$this->registerHook('actionProductSave') ||
			!Configuration::updateValue('MYMODULE_NAME', 'my friend')
		)
			return false;

		return true;
	}

	public function uninstall()
	{
		//delete the data added to the database during the installation ( MYMODULE_NAME configuration setting).
		if (!parent::uninstall() ||
			!Configuration::deleteByName('MYMODULE_NAME')
		)
			return false;

		return true;
	}

	/**
	 * [hookActionProductUpdate compare quantity in db with new quantity, if different send a mail w/ this new quantity]
	 * @param  [type] $params array('id_product' => (int)$this->id, 'product' => $this))
	 */
	public function hookActionProductUpdate($params)
	{
		// modified quantity
        $quantity = StockAvailable::getQuantityAvailableByProduct($params['id_product']);

        // if new quantity different from quantity in db, send mail
        if ($quantity !== $this->old_quantity) {        	
        	$name = Product::getProductName($params['id_product'], null, 2);
        	$reference = $params['product']->reference;

        	$data = array('{quantity}' => $quantity, '{name}' => $name, '{id}' => $params['id_product'], '{reference}' => $reference);

	        Mail::Send(2, // french language
	        'quantity', // email template file to be use
	        'Modification en stock pour le produit réference '.$reference, // email subject
	        $data, // content
	        'r3ds7ar@hotmail.com', // receiver email address 
	        NULL, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, false, NULL, NULL, NULL);
        }

        
	}
	
	/**
	 * [hookActionProductSave get quantity stored in db]
	 * @param  [type] $params array('id_product' => (int)$this->id, 'product' => $this))
	 */
	public function hookActionProductSave($params)
	{
        $this->old_quantity = StockAvailable::getQuantityAvailableByProduct($params['id_product']);
	}
}