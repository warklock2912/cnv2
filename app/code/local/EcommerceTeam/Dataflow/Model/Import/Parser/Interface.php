<?php

interface EcommerceTeam_Dataflow_Model_Import_Parser_Interface
{
    /**
     * Get item data
     *
     * @return array()
     */
    public function getData();

    /**
     * Reset cursor
     *
     * @return void
     */
    public function rewind();

    /**
     * Check is correct data structure
     *
     * @throws EcommerceTeam_Dataflow_Exception|Exception
     */
    public function validate();

    /**
     * Retrieve attributes codes, that we can process
     *
     * @return array
     */
    public function getAttributeCodes();
}