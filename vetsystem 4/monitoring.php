<?php
require_once 'config/db.php'; require_once 'config/auth.php'; require_login();
$page_title='Animal Monitoring'; $message=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $farm=(int)($_POST['farm_id']??0);$deaths=max(0,(int)($_POST['deaths']??0));$date=$_POST['observation_date']??date('Y-m-d');$symptoms=trim($_POST['symptoms']??'');$remarks=trim($_POST['remarks']??'');
 $check=$pdo->prepare('SELECT id FROM farms WHERE id=?');$check->execute([$farm]);
 if(!$check->fetch())$error='Please select a valid farm.';
 elseif(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date))$error='Please enter a valid observation date.';
 else{$s=$pdo->prepare('INSERT INTO monitoring(farm_id,deaths,observation_date,symptoms,remarks) VALUES(?,?,?,?,?)');$s->execute([$farm,$deaths,$date,$symptoms,$remarks]);$message='Monitoring record saved.';}
}
$farms=$pdo->query('SELECT f.id,f.farm_name,o.owner_name FROM farms f JOIN owners o ON o.id=f.owner_id ORDER BY o.owner_name')->fetchAll();
$rows=$pdo->query('SELECT m.*,f.farm_name,f.animal_type,f.barangay,o.owner_name FROM monitoring m JOIN farms f ON f.id=m.farm_id JOIN owners o ON o.id=f.owner_id ORDER BY m.observation_date DESC,m.id DESC')->fetchAll();
function risk_label($n){return $n>=8?'Critical':($n>=4?'Warning':'Good');}
include 'partials/header.php';
?>
<?php if($message):?><div class="alert success"><?=h($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=h($error)?></div><?php endif;?>
<section class="panel"><div class="panel-head"><h3>Record animal health monitoring</h3></div><form method="post"><div class="form-grid"><div class="wide"><label>Farm / Owner *</label><select name="farm_id" required><option value="">Select farm</option><?php foreach($farms as $f):?><option value="<?=$f['id']?>"><?=h($f['farm_name'].' — '.$f['owner_name'])?></option><?php endforeach;?></select></div><div><label>Number of deaths *</label><input type="number" name="deaths" min="0" required value="0"></div><div><label>Observation date *</label><input type="date" name="observation_date" required value="<?=date('Y-m-d')?>"></div><div class="wide"><label>Observed symptoms</label><textarea name="symptoms" placeholder="Describe observations without making a diagnosis."></textarea></div><div class="wide"><label>Remarks / action taken</label><textarea name="remarks"></textarea></div></div><button class="btn primary" type="submit">Save monitoring record</button></form></section>
<section class="panel"><div class="panel-head"><h3>Monitoring history</h3></div><div class="table-wrap"><table><thead><tr><th>Date</th><th>Owner</th><th>Farm</th><th>Animal</th><th>Barangay</th><th>Deaths</th><th>Status</th><th>Symptoms</th></tr></thead><tbody><?php foreach($rows as $r):$risk=risk_label((int)$r['deaths']);?><tr><td><?=h($r['observation_date'])?></td><td><?=h($r['owner_name'])?></td><td><?=h($r['farm_name'])?></td><td><?=h($r['animal_type'])?></td><td><?=h($r['barangay'])?></td><td><?=$r['deaths']?></td><td><span class="badge <?=$risk==='Good'?'green':($risk==='Warning'?'yellow-risk':'red')?>"><?=h($risk)?></span></td><td><?=h($r['symptoms'])?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="8" class="empty">No monitoring entries yet.</td></tr><?php endif;?></tbody></table></div></section>
<?php include 'partials/footer.php'; ?>
