<? if($_GET['a'] == 2) {
	$id = $_GET['id'];

	if($_POST['form'] == 'form') {
		$conteudo = $core->clear($_POST['conteudo']);
		$fechar = $core->clear($_POST['fechar']);
		$abrir = $core->clear($_POST['abrir']);
		$prosseguir = true;

		if(empty($conteudo)) {
			$form_return .= aviso_red("Preencha todos os campos.");
			$prosseguir = false;
		}

		if($fechar) {
			$up['status'] = 'fechado';
			$wh['id'] = $id;
			$update = $sqlActions->update($mdl_tabela, $up, $wh);
		}

		if($abrir) {
			$up['status'] = 'aberto';
			$wh['id'] = $id;
			$update = $sqlActions->update($mdl_tabela, $up, $wh);
		}

		if($prosseguir) {
			$in['id_ticket'] = $id;
			$in['conteudo'] = $conteudo;
			$in['autor'] = $core->autor;
			$in['data'] = $core->timestamp;
			$insert = $sqlActions->insert("tickets_resp", $in);

			if($insert) {
				$_ex = $conn->prepare("SELECT * FROM $mdl_tabela WHERE id = ? LIMIT 1");
				$_ex->bindValue(1, $id);
				$_ex->execute();
				$ex = $_ex->fetch();

				$core->sendNtfUser($ex['autor'], "[home]{$core->autor}[/home] respondeu seu ticket.", "/usuario/tickets/$id/{$core->trataurl($ex['titulo'])}", "ticket-reply");
				$core->logger("O usuário respondeu ao ticket [#$id].", "acao");

				$form_return .= aviso_green("Sucesso!");
				foreach($_POST as $nome_campo => $valor){ $_POST[$nome_campo] = '';}
			} else {
				$form_return .= aviso_red("Ocorreu um erro ao executar a ação. Código de erro: {$sqlActions->error}");
			}
		}
	}

	$_ex = $conn->prepare("SELECT * FROM $mdl_tabela WHERE id = ? LIMIT 1");
	$_ex->bindValue(1, $id);
	$_ex->execute();
	$ex = $_ex->fetch();

	if(!$ex) {
		$script_js .= register404();
	}
?>
<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?> - Ticket enviado</div>

	<div class="well well-lg">
		<? if($ex['status'] == 'aberto') { ?>
		<span class="label label-danger">Aberto</span>
		<? } else { ?>
		<span class="label label-success">Fechado</span>
		<? } ?>
		Ticket enviado por <b><?=$ex['autor'];?></b> em <b><?=date('d/m/Y H:i', $ex['data']);?></b><br><br>
		Assunto: <b><?=$core->clear($ex['assunto']);?></b><br><br>
		<?= $core->bbcode( nl2br($core->clear($ex['conteudo'])) );?>
	</div>
</div>

<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?> - Respostas ao ticket</div>

	<? $sql3 = $conn->prepare("SELECT * FROM tickets_resp WHERE id_ticket = ?");
	$sql3->bindValue(1, $ex['id']);
	$sql3->execute();
	$sql4 = $sql3->fetchAll();

	if(count($sql4) == 0) {
		echo aviso_red("Não há nenhuma resposta neste ticket.");
	}

	foreach ($sql4 as $atual) { ?>
	<div class="well well-lg">
		<? if($atual['autor'] == $ex['autor']) { ?>
		<span class="label label-success">Resposta enviada pelo autor do ticket</span>
		<? } ?>
		Resposta enviada por <b><?=$atual['autor'];?></b> em <b><?=date('d/m/Y H:i', $atual['data']);?></b>
		<br><br>
		<?= $core->bbcode( ($core->clear($atual['conteudo'])) );?>
	</div>
	<? } ?>
</div>

<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?> - Enviar resposta</div>

	<button class="btn btn-danger" onclick="deletar(this, 1);" rel="?p=<?=$p;?>&a=3&id=<?=$id;?>">Inativar</button><br><br>

	<? echo $form_return;

	$form = new Form('form-submit form-horizontal', '');

	$form->createTextarea('Conteúdo', 'conteudo');

	if($ex['status'] == 'aberto') {
		$form->createCheckbox('Fechar o ticket?', 'fechar');
	} else {
		$form->createCheckbox('Abrir o ticket?', 'abrir');
	}

	$form->generateForm();
	echo $form->form; ?>
</div>
<? } ?>

<? if($_GET['a'] == 339955) {
	$id = $_GET['id'];

	$ids = explode(',', $id);
	$ids = array_filter($ids);

	if(count($ids) > 0) {
		$delete = $conn->prepare("DELETE FROM $mdl_tabela WHERE id = ? LIMIT 1");
		$delete->bindParam(1, $id_atual);

		foreach ($ids as $id_atual) {
			$delete->execute();

			$core->logger("O usuário deletou o registro [#$id_atual - $mdl_tabela]", "acao");
		}
	} else {
		$delete_where['id'] = $id;
		$delete = $sqlActions->delete($mdl_tabela, $delete_where);

		$core->logger("O usuário deletou o registro [#$id_atual - $mdl_tabela]", "acao");
	}
} ?>

<? if($_GET['a'] == 3) {
	$id = $_GET['id'];

	$ids = explode(',', $id);
	$ids = array_filter($ids);

	if(count($ids) > 0) {
		$delete = $conn->prepare("UPDATE $mdl_tabela SET status = 'inativo' WHERE id = ? LIMIT 1");
		$delete->bindParam(1, $id_atual);

		foreach ($ids as $id_atual) {
			$delete->execute();

			$core->logger("O usuário deletou o ticket [#$id_atual]", "acao");
		}
	} else {
		$delete_where['id'] = $id;
		$delete = $conn->prepare("UPDATE $mdl_tabela SET status = 'inativo' WHERE id = ? LIMIT 1");
		$delete->bindValue(1, $id);
		$delete->execute();

		$core->logger("O usuário deletou o ticket [#$id]", "acao");
	}
} ?>

<? if($_GET['a'] == 4) {
	$id = $_GET['id'];

	$reset = $conn->query("ALTER TABLE $mdl_tabela AUTO_INCREMENT = 1;");
	$core->logger("O usuário resetou o AI de $mdl_tabela", "acao");

	echo "<script>location.replace('?p=$p');</script>";
} ?>

<? if($_GET['a'] == 9) {
	$id = $_GET['id'];

	$ativar = $conn->prepare("UPDATE $mdl_tabela SET status = 'ativo' WHERE id = ?");
	$ativar->bindValue(1, $id);
	$ativar->execute();

	$core->logger("O usuário ativou o registro [#$id - $mdl_tabela]", "acao");
} ?>

<? if($_GET['a'] == '') { ?>
<div class="box-content">
	<div class="title-section"><?=$mdl['nome'];?></div>
	<? if($core->allAccess()) { ?><a href="?p=<?=$_GET['p'];?>&a=4"><button class="btn btn-danger">Resetar AI [DEV]</button></a><? } ?>
	<button class="btn btn-info" onclick="searchShow();">Pesquisar</button>
	<? if($_POST['search'] == 'search') { ?><a href="?p=<?=$_GET['p'];?>"><button class="btn btn-warning">Limpar busca</button></a><? } ?>
	<br><br>

	<?php

	$search = getSearchForm();
	echo $search;

	?>

	<?
	$table = new Table('', true, $core->allAccess());
	$table->head(array('#', 'Assunto', 'Status', 'Autor', 'Data', 'Ações'));

	$table->startBody();

	$limite = 15;
	$pagina = $_GET['pag'];
	((!$pagina)) ? $pagina = 1 : '';
	$inicio = ($pagina * $limite) - $limite;

	$query = "$mdl_tabela ORDER BY id DESC";

	if($_POST['search'] == 'search') {
		$busca = $core->clear($_POST['busca']);
		$limite = 5000;

		$campo = "assunto";

		$query = "$mdl_tabela WHERE $campo LIKE ? ORDER BY id DESC";
		$sql = $conn->prepare("SELECT * FROM $query LIMIT $inicio,$limite");
		$sql->bindValue(1, '%'.$busca.'%');
		$sql->execute();

		$_rows = $conn->prepare("SELECT count(id) FROM $query");
		$_rows->bindValue(1, '%'.$busca.'%');
		$_rows->execute();
		$total_registros = $_rows->fetchColumn();

		echo '<div class="searching">Pesquisando por: <b>'.$busca.'</b></div>';
	} else {
		$sql = $conn->query("SELECT * FROM $query LIMIT $inicio,$limite");
		$total_registros = $core->getRows("SELECT * FROM $query");
	}

	while($sql2 = $sql->fetch()) {
		$stats = '';

		if($sql2['status'] == 'aberto') { $stats .= '<span class="label label-danger">Aberto</span> '; }
		if($sql2['status'] == 'fechado') { $stats .= '<span class="label label-success">Fechado</span> '; }

		$table->insertBody(array($sql2['id'], $core->clear($sql2['assunto']), $stats, $core->clear($sql2['autor']), $core->clear(date('d/m/Y H:i', $sql2['data'])), 'actions'), $sql2['status']);
	}

	$table->closeTable();
	echo $table->table;

	if($total_registros == 0) {
		echo aviso_red("Nenhum registro encontrado.");
	} else {
		echo '<ul class="pagination">';

		$total_paginas = ceil($total_registros / $limite);

		$links_laterais = ceil($limite / 2);

		$inicio = $pagina - $links_laterais;
		$limite = $pagina + $links_laterais;

		for ($i = $inicio; $i <= $limite; $i++){
			if ($i == $pagina) {
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
			} else {
				if ($i >= 1 && $i <= $total_paginas){
					$link = '?' . $_SERVER["QUERY_STRING"];
					$link = preg_replace('/(\\?|&)pag=.*?(&|$)/','',$link);
					echo '<li><a href="'.$link.'&pag='.$i.'">'.$i.'</a></li>';
				}
			}
		}

		echo '</ul>';
	} ?>

	<?php

	if($total_registros > 0) {
		$marked = getMarkedSelect($p, 3);
		echo $marked;
	}

	?>
</div>
<? } ?>