<?php
namespace Kernel\Services\Security\Interfaces;

interface MenuItemInterface {
    
    public function getId();
    public function getUrl(): string;
    public function getName(): string;
    public function getRightsForUser(UserInterface $user);
    public function getChilds(): MenuCollectionInterface;
    public function getParent(): MenuItemInterface;	
	public function setParent(MenuItemInterface $menuItem);
	public function addChild(MenuItemInterface $menuItem);
	public function hasParent(): bool;
	public function hasChilds(): bool;
    public function isExists() : bool;
}
