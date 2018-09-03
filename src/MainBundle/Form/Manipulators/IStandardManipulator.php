<?php
namespace MainBundle\Form\Manipulators;
/**
 * An interface for manipulating form fields and results.
 */
interface IStandardManipulator {
    public function getDefaultEntity();
    
    public function getPaginatorForDisplay();
    
    public function getUserEnvironmentVariables();
    
    public function getPlainResults();
    
    public function getPaginatedResults($startMultiplier);
    
}
