<?php

class ProgiCron { 


    public $error = 0;
    public $errors = array();
    public $db;

    /**
     *  Constructor
     *
     *  @param      DoliDB      $db         Database handler
     */
    public function __construct($db){$this->db = $db;}

    // 
    /**
     *  Appliquer x chiffres sur les comptes comptables
     *
     *  @param      int         $accountingaccount_length       Length wanted
     */
    public function setAccountingAccountLength($accountingaccount_length){

        $array_sql = array(
            "UPDATE llx_const SET llx_const.value = ".$accountingaccount_length." WHERE llx_const.name = 'ACCOUNTING_LENGTH_GACCOUNT';",
            "UPDATE llx_c_revenuestamp SET llx_c_revenuestamp.accountancy_code_buy = RPAD(llx_c_revenuestamp.accountancy_code_buy,".$accountingaccount_length.",0) WHERE llx_c_revenuestamp.accountancy_code_buy IS NOT NULL;",
            "UPDATE llx_c_revenuestamp SET llx_c_revenuestamp.accountancy_code_sell = RPAD(llx_c_revenuestamp.accountancy_code_sell,".$accountingaccount_length.",0) WHERE llx_c_revenuestamp.accountancy_code_sell IS NOT NULL;",
            "UPDATE llx_payment_various SET llx_payment_various.accountancy_code = RPAD(llx_payment_various.accountancy_code,".$accountingaccount_length.",0) WHERE llx_payment_various.accountancy_code IS NOT NULL;",
            "UPDATE llx_product SET llx_product.accountancy_code_buy = RPAD(llx_product.accountancy_code_buy,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_buy IS NOT NULL AND llx_product.accountancy_code_buy != '';",
            "UPDATE llx_product SET llx_product.accountancy_code_buy_intra = RPAD(llx_product.accountancy_code_buy_intra,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_buy_intra IS NOT NULL AND llx_product.accountancy_code_buy_intra != '';",
            "UPDATE llx_product SET llx_product.accountancy_code_buy_export = RPAD(llx_product.accountancy_code_buy_export,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_buy_export IS NOT NULL AND llx_product.accountancy_code_buy_export != '';",
            "UPDATE llx_product SET llx_product.accountancy_code_sell = RPAD(llx_product.accountancy_code_sell,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_sell IS NOT NULL AND llx_product.accountancy_code_sell != '';",
            "UPDATE llx_product SET llx_product.accountancy_code_sell = RPAD(llx_product.accountancy_code_sell,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_sell IS NOT NULL AND llx_product.accountancy_code_sell != '';",
            "UPDATE llx_product SET llx_product.accountancy_code_sell_export = RPAD(llx_product.accountancy_code_sell_export,".$accountingaccount_length.",0) WHERE llx_product.accountancy_code_sell_export IS NOT NULL AND llx_product.accountancy_code_sell_export != '';",
            "UPDATE llx_c_tva SET llx_c_tva.accountancy_code_buy = RPAD(llx_c_tva.accountancy_code_buy,".$accountingaccount_length.",0) WHERE llx_c_tva.accountancy_code_buy IS NOT NULL;",
            "UPDATE llx_c_tva SET llx_c_tva.accountancy_code_sell = RPAD(llx_c_tva.accountancy_code_sell,".$accountingaccount_length.",0) WHERE llx_c_tva.accountancy_code_sell IS NOT NULL;",
            "UPDATE llx_c_type_fees SET llx_c_type_fees.accountancy_code = RPAD(llx_c_type_fees.accountancy_code,".$accountingaccount_length.",0) WHERE llx_c_type_fees.accountancy_code IS NOT NULL;",
            "UPDATE llx_accounting_account SET llx_accounting_account.account_number = RPAD(llx_accounting_account.account_number,".$accountingaccount_length.",0) WHERE llx_accounting_account.account_number IS NOT NULL;",
            "UPDATE llx_accounting_bookkeeping SET llx_accounting_bookkeeping.numero_compte = RPAD(llx_accounting_bookkeeping.numero_compte,".$accountingaccount_length.",0) WHERE llx_accounting_bookkeeping.numero_compte IS NOT NULL;",
            "UPDATE llx_accounting_bookkeeping_tmp SET llx_accounting_bookkeeping_tmp.numero_compte = RPAD(llx_accounting_bookkeeping_tmp.numero_compte,".$accountingaccount_length.",0) WHERE llx_accounting_bookkeeping_tmp.numero_compte IS NOT NULL;",
            "UPDATE llx_bank_account SET llx_bank_account.account_number = RPAD(llx_bank_account.account_number,".$accountingaccount_length.",0) WHERE llx_bank_account.account_number IS NOT NULL;",
            "UPDATE llx_c_chargesociales SET llx_c_chargesociales.accountancy_code = RPAD(llx_c_chargesociales.accountancy_code,".$accountingaccount_length.",0) WHERE llx_c_chargesociales.accountancy_code IS NOT NULL;",
            "UPDATE llx_c_paiement SET llx_c_paiement.accountancy_code = RPAD(llx_c_paiement.accountancy_code,".$accountingaccount_length.",0) WHERE llx_c_paiement.accountancy_code IS NOT NULL;",
            "UPDATE llx_c_paiement_temp SET llx_c_paiement_temp.accountancy_code = RPAD(llx_c_paiement_temp.accountancy_code,".$accountingaccount_length.",0) WHERE llx_c_paiement_temp.accountancy_code IS NOT NULL;",
        );

        $this->output = '';
        foreach($array_sql as $sql_key => $sql):
            $request_num = intval($sql_key) + 1;
            $result = $this->db->query($sql);
            if(!$result): 
                $this->error++; 
                $this->errors[] = 'Erreur requête N'.$request_num;
                $this->errors[] = $this->db->lasterror;
           endif;
        endforeach;

        $num_success = count($array_sql) - $this->error;
        $this->output.= $num_success.'/'.count($array_sql).' requêtes OK';

        if($this->error > 0): 
            $this->output.= '<br>Erreurs:';
            return -1;
        endif;        
        return 0;
    }


}

?>