/**
 * in_compare
 *
 * Snippet for check is some item in compare
 *
 * @category    snippet
 * @version     0.1
 * @internal    @properties
 * @internal    @modx_category Shop
 * @internal   	@installset base, sample 
 */


$id = isset($id)?$id:$modx->documentIdentifier;
$ids = isset($_COOKIE['shkCompareIds'])?$_COOKIE['shkCompareIds']:'';
if (empty($ids)) {
    return '0';
}
$ids = explode(',', $ids);

  if(in_array($id,$ids)){
      return '1';
  }
  else{
      return '0';
  }
?>
