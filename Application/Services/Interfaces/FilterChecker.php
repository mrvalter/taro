<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Services\Interfaces;

/**
 *
 * @author sworion
 */
interface filterChecker {
    
    public function execute(&$value);
    public function getError();
    
}
