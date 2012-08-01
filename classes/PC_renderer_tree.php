<?php
abstract class PC_renderer_tree extends PC_base {
	abstract public function Create() {}
	abstract public function Get() {}
	abstract public function Delete() {}
	abstract public function Move() {}
	abstract public function Get_preview_link() {}
}