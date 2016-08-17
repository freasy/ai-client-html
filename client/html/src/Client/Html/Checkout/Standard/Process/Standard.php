<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Checkout\Standard\Process;


// Strings for translation
sprintf( 'process' );


/**
 * Default implementation of checkout process HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/checkout/standard/process/standard/subparts
	 * List of HTML sub-clients rendered within the checkout standard process section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $subPartPath = 'client/html/checkout/standard/process/standard/subparts';
	private $subPartNames = array();
	private $cache;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string HTML code
	 */
	public function getBody( $uid = '', array &$tags = array(), &$expire = null )
	{
		$view = $this->getView();

		if( !in_array( $view->get( 'standardStepActive' ), array( 'order', 'process' ) ) ) {
			return '';
		}

		$view = $this->setViewParams( $view, $tags, $expire );

		$html = '';
		foreach( $this->getSubClients() as $subclient ) {
			$html .= $subclient->setView( $view )->getBody( $uid, $tags, $expire );
		}
		$view->processBody = $html;

		/** client/html/checkout/standard/process/standard/template-body
		 * Relative path to the HTML body template of the checkout standard process client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating code for the HTML page body
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/process/standard/template-header
		 */
		$tplconf = 'client/html/checkout/standard/process/standard/template-body';
		$default = 'checkout/standard/process-body-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string|null String including HTML tags for the header on error
	 */
	public function getHeader( $uid = '', array &$tags = array(), &$expire = null )
	{
		$view = $this->getView();

		if( !in_array( $view->param( 'standardStepActive' ), array( 'order', 'process' ) ) ) {
			return '';
		}

		$view = $this->setViewParams( $view, $tags, $expire );

		$html = '';
		foreach( $this->getSubClients() as $subclient ) {
			$html .= $subclient->setView( $view )->getHeader( $uid, $tags, $expire );
		}
		$view->processHeader = $html;

		/** client/html/checkout/standard/process/standard/template-header
		 * Relative path to the HTML header template of the checkout standard process client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the HTML code that is inserted into the HTML page header
		 * of the rendered page in the frontend. The configuration string is the
		 * path to the template file relative to the templates directory (usually
		 * in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating code for the HTML page head
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/process/standard/template-body
		 */
		$tplconf = 'client/html/checkout/standard/process/standard/template-header';
		$default = 'checkout/standard/process-header-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** client/html/checkout/standard/process/decorators/excludes
		 * Excludes decorators added by the "common" option from the checkout standard process html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/html/common/decorators/default" before they are wrapped
		 * around the html client.
		 *
		 *  client/html/checkout/standard/process/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/standard/process/decorators/global
		 * @see client/html/checkout/standard/process/decorators/local
		 */

		/** client/html/checkout/standard/process/decorators/global
		 * Adds a list of globally available decorators only to the checkout standard process html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/checkout/standard/process/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/standard/process/decorators/excludes
		 * @see client/html/checkout/standard/process/decorators/local
		 */

		/** client/html/checkout/standard/process/decorators/local
		 * Adds a list of local decorators only to the checkout standard process html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Checkout\Decorator\*") around the html client.
		 *
		 *  client/html/checkout/standard/process/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Checkout\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2015.08
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/checkout/standard/process/decorators/excludes
		 * @see client/html/checkout/standard/process/decorators/global
		 */

		return $this->createSubClient( 'checkout/standard/process/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given order.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$view = $this->getView();
		$errors = $view->get( 'standardErrorList', array() );

		if( !in_array( $view->param( 'c_step' ), array( 'order', 'process' ) ) || !empty( $errors ) ) {
			return;
		}

		$context = $this->getContext();
		$session = $context->getSession();
		$orderid = $session->get( 'aimeos/orderid' );
		$config = array( 'absoluteUri' => true, 'namespace' => false );

		try
		{
			$orderItem = \Aimeos\MShop\Factory::createManager( $context, 'order' )->getItem( $orderid );

			if( ( $code = $this->getOrderServiceCode( $orderItem->getBaseId() ) ) !== null )
			{
				$serviceItem = $this->getServiceItem( $code );

				$serviceManager = \Aimeos\MShop\Factory::createManager( $context, 'service' );
				$provider = $serviceManager->getProvider( $serviceItem );

				$params = array( 'code' => $serviceItem->getCode(), 'orderid' => $orderid );
				$urls = array(
					'payment.url-self' => $this->getUrlSelf( $view, $params + array( 'c_step' => 'process' ), array() ),
					'payment.url-success' => $this->getUrlConfirm( $view, $params, $config ),
					'payment.url-update' => $this->getUrlUpdate( $view, $params, $config ),
					'client.ipaddress' => $view->request()->getClientAddress(),
				);
				$provider->injectGlobalConfigBE( $urls );

				if( ( $form = $provider->process( $orderItem, $view->param() ) ) === null )
				{
					$msg = sprintf( 'Invalid process response from service provider with code "%1$s"', $serviceItem->getCode() );
					throw new \Aimeos\Client\Html\Exception( $msg );
				}

				$view->standardUrlNext = $form->getUrl();
				$view->standardMethod = $form->getMethod();
				$view->standardProcessParams = $form->getValues();
				$view->standardUrlExternal = $form->getExternal();
			}
			else
			{
				$view->standardUrlNext = $this->getUrlConfirm( $view, array(), array() );
				$view->standardMethod = 'GET';
			}


			parent::process();
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->getI18n()->dt( 'client', $e->getMessage() ) );
			$view->standardErrorList = $view->get( 'standardErrorList', array() ) + $error;
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->getI18n()->dt( 'controller/frontend', $e->getMessage() ) );
			$view->standardErrorList = $view->get( 'standardErrorList', array() ) + $error;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->standardErrorList = $view->get( 'standardErrorList', array() ) + $error;
		}
		catch( \Exception $e )
		{
			$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );

			$error = array( $context->getI18n()->dt( 'client', 'A non-recoverable error occured' ) );
			$view->standardErrorList = $view->get( 'standardErrorList', array() ) + $error;
		}
	}


	/**
	 * Returns the payment service code from the order with the given base ID.
	 *
	 * @param string $baseid ID of the order base item
	 * @return string|null Code of the service item or null if not found
	 */
	protected function getOrderServiceCode( $baseid )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'order/base/service' );

		$search = $manager->createSearch();
		$expr = array(
			$search->compare( '==', 'order.base.service.baseid', $baseid ),
			$search->compare( '==', 'order.base.service.type', \Aimeos\MShop\Order\Item\Base\Service\Base::TYPE_PAYMENT ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$result = $manager->searchItems( $search );

		if( ( $item = reset( $result ) ) !== false ) {
			return $item->getCode();
		}
	}


	/**
	 * Returns the payment service item for the given code.
	 *
	 * @param string $code Unique service code
	 * @throws \Aimeos\Client\Html\Exception If no service item for this code is found
	 * @return \Aimeos\MShop\Service\Item\Iface Service item object
	 */
	protected function getServiceItem( $code )
	{
		$serviceManager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'service' );

		$search = $serviceManager->createSearch();
		$expr = array(
			$search->compare( '==', 'service.code', $code ),
			$search->compare( '==', 'service.type.code', 'payment' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		$result = $serviceManager->searchItems( $search );

		if( ( $serviceItem = reset( $result ) ) === false )
		{
			$msg = sprintf( 'No service for code "%1$s" found', $code );
			throw new \Aimeos\Client\Html\Exception( $msg );
		}

		return $serviceItem;
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames()
	{
		return $this->getContext()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Returns the URL to the confirm page.
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $params Parameters that should be part of the URL
	 * @param array $config Default URL configuration
	 * @return string URL string
	 */
	protected function getUrlConfirm( \Aimeos\MW\View\Iface $view, array $params, array $config )
	{
		/** client/html/checkout/confirm/url/target
		 * Destination of the URL where the controller specified in the URL is known
		 *
		 * The destination can be a page ID like in a content management system or the
		 * module of a software development framework. This "target" must contain or know
		 * the controller that should be called by the generated URL.
		 *
		 * @param string Destination of the URL
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/confirm/url/controller
		 * @see client/html/checkout/confirm/url/action
		 * @see client/html/checkout/confirm/url/config
		 */
		$target = $view->config( 'client/html/checkout/confirm/url/target' );

		/** client/html/checkout/confirm/url/controller
		 * Name of the controller whose action should be called
		 *
		 * In Model-View-Controller (MVC) applications, the controller contains the methods
		 * that create parts of the output displayed in the generated HTML page. Controller
		 * names are usually alpha-numeric.
		 *
		 * @param string Name of the controller
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/confirm/url/target
		 * @see client/html/checkout/confirm/url/action
		 * @see client/html/checkout/confirm/url/config
		 */
		$cntl = $view->config( 'client/html/checkout/confirm/url/controller', 'checkout' );

		/** client/html/checkout/confirm/url/action
		 * Name of the action that should create the output
		 *
		 * In Model-View-Controller (MVC) applications, actions are the methods of a
		 * controller that create parts of the output displayed in the generated HTML page.
		 * Action names are usually alpha-numeric.
		 *
		 * @param string Name of the action
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/confirm/url/target
		 * @see client/html/checkout/confirm/url/controller
		 * @see client/html/checkout/confirm/url/config
		 */
		$action = $view->config( 'client/html/checkout/confirm/url/action', 'confirm' );

		/** client/html/checkout/confirm/url/config
		 * Associative list of configuration options used for generating the URL
		 *
		 * You can specify additional options as key/value pairs used when generating
		 * the URLs, like
		 *
		 *  client/html/<clientname>/url/config = array( 'absoluteUri' => true )
		 *
		 * The available key/value pairs depend on the application that embeds the e-commerce
		 * framework. This is because the infrastructure of the application is used for
		 * generating the URLs. The full list of available config options is referenced
		 * in the "see also" section of this page.
		 *
		 * @param string Associative list of configuration options
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/confirm/url/target
		 * @see client/html/checkout/confirm/url/controller
		 * @see client/html/checkout/confirm/url/action
		 * @see client/html/url/config
		 */
		$config = $view->config( 'client/html/checkout/confirm/url/config', $config );

		return $view->url( $target, $cntl, $action, $params, array(), $config );
	}


	/**
	 * Returns the URL to the current page.
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $params Parameters that should be part of the URL
	 * @param array $config Default URL configuration
	 * @return string URL string
	 */
	protected function getUrlSelf( \Aimeos\MW\View\Iface $view, array $params, array $config )
	{
		/** client/html/checkout/standard/url/target
		 * Destination of the URL where the controller specified in the URL is known
		 *
		 * The destination can be a page ID like in a content management system or the
		 * module of a software development framework. This "target" must contain or know
		 * the controller that should be called by the generated URL.
		 *
		 * @param string Destination of the URL
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/url/controller
		 * @see client/html/checkout/standard/url/action
		 * @see client/html/checkout/standard/url/config
		 */
		$target = $view->config( 'client/html/checkout/standard/url/target' );

		/** client/html/checkout/standard/url/controller
		 * Name of the controller whose action should be called
		 *
		 * In Model-View-Controller (MVC) applications, the controller contains the methods
		 * that create parts of the output displayed in the generated HTML page. Controller
		 * names are usually alpha-numeric.
		 *
		 * @param string Name of the controller
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/url/target
		 * @see client/html/checkout/standard/url/action
		 * @see client/html/checkout/standard/url/config
		 */
		$cntl = $view->config( 'client/html/checkout/standard/url/controller', 'checkout' );

		/** client/html/checkout/standard/url/action
		 * Name of the action that should create the output
		 *
		 * In Model-View-Controller (MVC) applications, actions are the methods of a
		 * controller that create parts of the output displayed in the generated HTML page.
		 * Action names are usually alpha-numeric.
		 *
		 * @param string Name of the action
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/url/target
		 * @see client/html/checkout/standard/url/controller
		 * @see client/html/checkout/standard/url/config
		 */
		$action = $view->config( 'client/html/checkout/standard/url/action', 'index' );

		/** client/html/checkout/standard/url/config
		 * Associative list of configuration options used for generating the URL
		 *
		 * You can specify additional options as key/value pairs used when generating
		 * the URLs, like
		 *
		 *  client/html/<clientname>/url/config = array( 'absoluteUri' => true )
		 *
		 * The available key/value pairs depend on the application that embeds the e-commerce
		 * framework. This is because the infrastructure of the application is used for
		 * generating the URLs. The full list of available config options is referenced
		 * in the "see also" section of this page.
		 *
		 * @param string Associative list of configuration options
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/standard/url/target
		 * @see client/html/checkout/standard/url/controller
		 * @see client/html/checkout/standard/url/action
		 * @see client/html/url/config
		 */
		$config = $view->config( 'client/html/checkout/standard/url/config', $config );

		return $view->url( $target, $cntl, $action, $params, array(), $config );
	}


	/**
	 * Returns the URL to the update page.
	 *
	 * @param \Aimeos\MW\View\Iface $view View object
	 * @param array $params Parameters that should be part of the URL
	 * @param array $config Default URL configuration
	 * @return string URL string
	 */
	protected function getUrlUpdate( \Aimeos\MW\View\Iface $view, array $params, array $config )
	{
		/** client/html/checkout/update/url/target
		 * Destination of the URL where the controller specified in the URL is known
		 *
		 * The destination can be a page ID like in a content management system or the
		 * module of a software development framework. This "target" must contain or know
		 * the controller that should be called by the generated URL.
		 *
		 * @param string Destination of the URL
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/update/url/controller
		 * @see client/html/checkout/update/url/action
		 * @see client/html/checkout/update/url/config
		 */
		$target = $view->config( 'client/html/checkout/update/url/target' );

		/** client/html/checkout/update/url/controller
		 * Name of the controller whose action should be called
		 *
		 * In Model-View-Controller (MVC) applications, the controller contains the methods
		 * that create parts of the output displayed in the generated HTML page. Controller
		 * names are usually alpha-numeric.
		 *
		 * @param string Name of the controller
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/update/url/target
		 * @see client/html/checkout/update/url/action
		 * @see client/html/checkout/update/url/config
		 */
		$cntl = $view->config( 'client/html/checkout/update/url/controller', 'checkout' );

		/** client/html/checkout/update/url/action
		 * Name of the action that should create the output
		 *
		 * In Model-View-Controller (MVC) applications, actions are the methods of a
		 * controller that create parts of the output displayed in the generated HTML page.
		 * Action names are usually alpha-numeric.
		 *
		 * @param string Name of the action
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/update/url/target
		 * @see client/html/checkout/update/url/controller
		 * @see client/html/checkout/update/url/config
		 */
		$action = $view->config( 'client/html/checkout/update/url/action', 'update' );

		/** client/html/checkout/update/url/config
		 * Associative list of configuration options used for generating the URL
		 *
		 * You can specify additional options as key/value pairs used when generating
		 * the URLs, like
		 *
		 *  client/html/<clientname>/url/config = array( 'absoluteUri' => true )
		 *
		 * The available key/value pairs depend on the application that embeds the e-commerce
		 * framework. This is because the infrastructure of the application is used for
		 * generating the URLs. The full list of available config options is referenced
		 * in the "see also" section of this page.
		 *
		 * @param string Associative list of configuration options
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/checkout/update/url/target
		 * @see client/html/checkout/update/url/controller
		 * @see client/html/checkout/update/url/action
		 * @see client/html/url/config
		 */
		$config = $view->config( 'client/html/checkout/update/url/config', $config );

		return $view->url( $target, $cntl, $action, $params, array(), $config );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	protected function setViewParams( \Aimeos\MW\View\Iface $view, array &$tags = array(), &$expire = null )
	{
		if( !isset( $this->cache ) )
		{
			$view->standardUrlPayment = $this->getUrlSelf( $view, array( 'c_step' => 'payment' ), array() );

			$this->cache = $view;
		}

		return $this->cache;
	}
}
