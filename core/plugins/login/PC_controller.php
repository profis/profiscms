<?php
/* Writing controllers for ProfisCMS4
 *
 * - class name is made of `PC_controller_` prefix and same name as this plugin directory name.
 *   i.e. if plugin is based in /public/admin/plugins/dummy/, then controllers' class must be named `PC_dummy`
 *
 * - another important thing to note is that this class must extend predefined `PC_controller` class which contains
 *   abstraction layer and predefined methods for all controllers, otherwise system will reject loading your controller.
 *
 * - You can debug $this object to see what predefined data you can use (just uncomment first line in the constructor).
 *
 * - Use $this->core->routes for internal plugin paging.
 *
 * - What CMS does when loading page that is configured to use this controller, is calling `Process($data)` method of this class.
 *    - $data parameter contains all information about page that is loaded from the database table `pages` by the given route.
 *
 **/
final class PC_controller_login extends PC_controller {
	//$data contains all information about the loaded page from the database table `pages`
	//$data masyve sudeta visa puslapio, kuris naudoja pasirinkta route'a, informacija.
	public function Process($data) {
		/*print_pre($this);
		print_pre($data); //lets see what's inside
		//here we're setting page <title> text
		$this->core->title = 'custom title';
		//and main page body source, which you can generate using your own methods in this controller class
		//by looking at $this->core->routes for internal paging
		$this->core->text = print_pre($this->core->routes, false);*/
		$this->Render();
	}
}