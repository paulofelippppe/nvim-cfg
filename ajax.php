<?
/*
	Silvio Felippe
	Qui Dez 25 16:49:07 2008


	Exemplo utilizando com a funcão ajax() do paulão:	
		?>
		<a onclick="testeAjax(false)" id="listaVagas">SPAN</a><br>
		<a onclick="testeAjax()" id="listaVagas">MODAL</a><br>
		<span id="spanTeste"></span>
		<script>

		function testeAjax(modal = true) {
			ajax("teste", { p1: 12, acesso: "<?=$acesso?>" }, function (res) {
				if (modal) {
					createModal(res).show(true)
					return
				}
				// apresenta no SPAN
				$('#spanTeste').innerHTML = res
			})
		}




*/
ini_set("display_errors", 0);


// evita injeção de código malicioso
// Silvio - 12/10/2023 - dia das crianças
foreach ($GLOBALS as $key => $value) {
    if (is_string($value)) {
        $value = str_replace("'", "",$value);
        $value = str_replace('"', "",$value);
        $GLOBALS[$key] = stripslashes($value);
        
    }
}

extract($_GET);
extract($_POST);
if (isset($data)) extract((array) json_decode($data));

$segurancaAjaaax=1;
require("sessao.php");
/*
require("config.php");
require_once("codeSp5/codeSp5.php");
require("book2.php");
*/

?>

<?

if ($op=='teste') {
	echo "<font color=red>Chamada de função OK.</font><br> op=$op p1=$p1 p2=$p2 p3=$p3 p4=$p4 acesso=$acesso";

}


if ($op=='Cancelar') {
	echo "";
}



if ($op=='agendaNome') {
	$nome = $p1;
	$nome = aspas($nome);
	$nome = str_replace(" ", "%", $nome);
	$res = sqli("select id, nome,celular from estudante where nome like '%$nome%' order by nome limit 4");
    
    if ($nome) {
        ?>
        <div class="list-group rounded-0 shadow-sm bg-whitesmoke"><?
            while ($reg = $res->fetch_object()) {
                echo <<<EOT
                <p class="list-group-item mb-0 p-2">
                    <a href="javascript:void(0)" onclick="server('agendaSetNome','spanNome','$reg->id')">$reg->nome</a><br>
                    $reg->celular
                </p>
                EOT;
            } ?>
        </div>
        <?
    }
}


if ($op=='agendaSetNome') {
	$id = $p1;
	$nome = objetoi("select nome from estudante where id = '$id'");
	
    echo <<<EOT
    <div class="list-group rounded-0 shadow-sm bg-whitesmoke">
        <p class="list-group-item mb-0 p-2">
            Cadastro: <strong>$nome->nome</strong>
            <input type="hidden" name="idestudante" value="$id">
        </p>
    </div>
    EOT;

	$now = time(); sqli("update estudante set acessado = '$now' where id = '$id'");
}




if ($op=="prese") {
	/// lista de chamadas
	$vid = $p1;
	$verde = "#207412";
	$vermelho = "#d50909";
	$sta = objetoi("select status from presenca where id = '$vid'");
	if ($sta->status=="p") { 
		sqli("update presenca set status = 'f' where id = '$vid'");
		echo "<font color=$vermelho>Ausente</font>";
	} else {
		sqli("update presenca set status = 'p' where id = '$vid'");
		echo "<font color=$verde>Presente</font>";

	}
}




if ($op=='infoFolha') {
	$estag = objetoi("select nome,idempresa,idsecretaria from estagiario where id='$idestagiario'");
	$boleto = objetoi("select boleto.*, estaboleto.valor as valordoaluno from estaboleto,boleto where estaboleto.idestagiario='$idestagiario' and estaboleto.referencia='$referencia' and boleto.id = estaboleto.boleto and !boleto.lixeira and !boleto.cancelado order by id desc limit 1");
	echo "<h3>$estag->nome</h3>";
	
	$bole = 'BOLETO';
	if ($boleto->pix) $bole='PIX';

	echo "<B>- FECHAMENTO DA FOLHA: </B><br>";
	echo "<ul>";
	if (!$boleto->id) {
		$outroBole = objetoi("select boleto.id,boleto.emissao from boleto where idempresa='$estag->idempresa' and referencia = '$referencia' and !boleto.lixeira and !boleto.cancelado ");
		if (!$outroBole->id) {
			echo "<p>Fatura não preenchida";
		} else {
			echo "<p>Estagiário não consta da folha faturada em ".date("d/m/Y H:i:s",strtotime($outroBole->emissao));
		}

	}
	if ($boleto->id) {
		echo "Faturado em ".date("d/m/Y H:i:s",strtotime($boleto->emissao));
		//if ($boleto->pix) echo " para pgto através de PIX";
		echo ". ";
		if (!$boleto->pix) {
			echo "<br>Vencimento do Boleto: ".dtoc($boleto->venc) .".";
		}
	}
	echo "</ul>";

	if ($boleto->id) {
	echo "<B>- $bole: </B><br>";
	echo "<ul>";
		if (!$boleto->recebido) echo "<font color=red>A receber. </font>";
		else {
			echo "<font color=green>Pago ";
			if (dtoc($boleto->datapgto)) echo "em ".dtoc($boleto->datapgto);
			echo "</font>";
		}
		//echo "<br>Valor do estagiário: R$".number_format($boleto->valordoaluno,2);
	echo "</ul>";

	}


	echo "<B>- REMESSA: </B><br>";
	echo "<ul>";	
	$remessaStat = '';
	$rem = objetoi("select * from estaremessa where idestagiario = '$idestagiario' and referencia = '$referencia' and !descartado order by id desc limit 1");
	if ($rem->id) {
		$remessaStat = "Remessa enviada ";
		if ($rem->data>'0000-00-00 00:00:00') {
			$remessaStat .= " em: ".date("d/m/Y", strtotime($rem->data)) . " às ".date("H", strtotime($rem->data)). "h.";
		}
		echo "$remessaStat";
		// comprovante
		echo "<br><font color=#8233B3>". ucfirst($rem->resposta)."</font>";


	} else {
		echo "Não identificada.";
	}	
		// solicitado por Bruna em Janeiro de 2022
		//pagamento realizado manualmente
		$comprov = objetoi("select manual from comprovante where idestagiario = '$idestagiario' and referencia = '$referencia'");
		if ($comprov->manual) { echo "<br>Pagamento realizado manualmente"; }
		//echo "select manual from comprovante where idestagiario = '$idestagiario' and referencia = '$referencia'";
	echo "</ul>";


	
	$retido = objetoi("select resposta as motivo from estaremessa where idestagiario = '$idestagiario' and referencia = '$referencia' order by id desc limit 1");
	if ($retido->id) {
		echo "<B>- PGTO RETIDO: </B><br>";
		echo "<ul>";	
		echo "Motivo: <i>$retido->motivo</i>";
		/*
		if ($retido->autorizado) {
			echo "<br><font color=#8233B3>Pgto autorizado: $retido->autorizado</font>";
		}
*/
		echo "</ul>";
	}	
	


	/*
- PAGAMENTO RETIDO: se foi enviado para os pagamentos retidos, qual o motivo (docs pendentes)
tudo isso do mês em processamento.


	*/

}




if ($op=="notaOk") {
	$status = $p1;

	$ap2 = explode(",",$p2);
	$idempresa = $ap2[0];
	$referencia = $ap2[1];
	$username = $ap2[2];
	$idsecretaria = $ap2[3];
	if (!$idempresa) {
		echo "<span class=red>Empresa não identificada.</span>";
	} else {
	
		if ($status==true) {
			$data = date("Y-m-d");
			$hora = date("H:i");
		} else {
			$data="";
			$hora="";
			$username='';
			echo "";
		}
		echo dtoc($data);
		echo " $hora $username";

		$nf = objetoi("select id from notafiscal where idempresa='$idempresa' and referencia='$referencia' and idsecretaria='$idsecretaria' limit 1");
		if (!$nf->id) {
			// cria um registro para registrar a data
			sqli("insert into notafiscal set idempresa='$idempresa', idsecretaria='$idsecretaria', referencia = '$referencia',hora='$hora', usuario='$username' ");
		}

		sqli("update notafiscal set emissao='$data',hora='$hora', usuario='$username' where idempresa = '$idempresa' and idsecretaria='$idsecretaria' and referencia ='$referencia'");
		//
	}
}
	

if ($op=="sethorario") {

	$idmatricula = $p2;
	$idhorario = $p3;
	$iduser=$p4;
	$sele = objetoi("select id from aulaporsemana where idmatricula='$idmatricula' and idhorario = '$idhorario' limit 1");
	
	if (!$sele->id) {
		sqli("insert into aulaporsemana (idmatricula, idhorario) value ('$idmatricula', '$idhorario')");
	} else {
		$ap = objetoi("select * from aulaporsemana where idmatricula = '$idmatricula'");

		if ($ap->id) {
			sqli("insert into aulaporsemanahist set idmatricula = '$idmatricula', idhorario='$ap->idhorario', data=curdate(), iduser='$iduser', motivo=''");
		}
		sqli("delete from aulaporsemana where idhorario = '$idhorario' and idmatricula = '$idmatricula'");
	}
	
}

        ?><?

if ($op=="openobs") {
	$vid ;
	//$pre = objetoi("select obs,idaula from  presenca where presenca.id = '$vid'");
	$pre = objetoi("select presenca.obs,idaula, data from presenca,aula where presenca.id = '$vid' and aula.id = presenca.idaula");
	$au = objetoi("select idinstrutor from aula where id = '$pre->idaula'");
        ?>
        
	<form method=post action=ajax.php?op=salvarobs&vid=<?=$vid?>>
	<div align=center>
	<h4>Observação</h4>
	<textarea name=obs rows=10 cols=45 ><?=$pre->obs?></textarea>
	<br>
	<? if (($xidu == $au->idinstrutor and $pre->data==date("Y-m-d")) or $su) { ?>
	<input type=submit name=saveobs class='btn btn-sm btn-success' value="Salvar">
	<? } ?>
	</div>
	</form>
<?
}




if ($op=="salvarnota") {
	$nota = val($nota);
	$vid = val($vid);
	sqli("update matricula set nota = '$nota' where id = '$vid'");
	?><script>window.close();</script><?
}


if ($op=="salvarobs") {
	$obs = aspas($obs);
	$vid = val($vid);
	sqli("update presenca set obs = '$obs' where id = '$vid'");
	?><script>window.close();</script><?
}


if ($op=='vigenciacontrato') {
	//echo "teste $p1, $p2, $p3";
	$vigencia1 = $p1;
	$codVaga = $p2;
	$codEstagiario = $p3;
	$vigencia2 = date("Y-m-d",strtotime("$vigencia1 +1 year"));
	$emp = objetoi("select empresa.id as idempresa from vagas,empresa where vagas.id = '$codVaga' and concnpj=empresa.cnpj");
	//echo "id empresa=$emp->idempresa --";
	$dataMaxima = getMaxDataTermino($codEstagiario,$emp->idempresa,$idestagiario);
	if ($vigencia2>$dataMaxima) $vigencia2=$dataMaxima;
	?>
	<input type=date name=vigencia2 max='<?=$dataMaxima?>' value='<?=$vigencia2?>' required>
	<?
	echo " Data máxima ".dtoc($dataMaxima);
}







if ($op=='editcoment') {
	$vid = aspas($p1);
	$iduser = val($p2);
	$reg = objetoi("select * from comentario where id = '$vid'");
	
?>
	</form>
	<form name=formu<?=$vid?>>
    Editando:<br>
    <textarea  name=comentario rows=5 cols=40><?=$reg->comentario?></textarea>
    <a href="javascript:server('saveEditComentario','coment<?=$vid?>','<?=$vid?>',formu<?=$vid?>.comentario.value,'<?=$iduser?>')" class='btn btn-sm btn-success'>Salvar</a>
    <a href="javascript:server('excluirComentario','coment<?=$vid?>','<?=$vid?>')" onclick="return confirm('Tem certeza que deseja excluir?') " class='btn btn-sm btn-danger'>Excluir</a>

	</form>
	<?
}


if ($op=='excluirComentario') {
	$vid = val($p1);
	$reg = objetoi("select * from comentario where id = '$reg->id'");

	sqli("delete from comentario where id = '$vid' ");
	echo "Comentário apagado.";
	log2(0,"Apagado comentário $reg->origem $reg->comentario");
}



if ($op=='saveEditComentario') {
	$vid = val($p1);
	$texto = aspas($p2);
	$iduser = val($p3);
	sqli("update comentario set comentario='$texto' where id='$vid'");
	$reg = objetoi("select * from comentario where id = '$vid'");
    $server = "<a href=\"javascript:server('editcoment','coment$reg->id','$reg->id','$iduser')\"><img src=images/tools/note_edit.png width=16></a>";
    ?>
    <div style='margin:5px; padding:5px; font-size:0.9em;'>
	<span id=coment<?=$reg->id?>>
		<?=$reg->comentario?>
        <br><b><?=$reg->username?></b> <span class=gray><?=$reg->name?></span>
        <span class=obs><?=date("d/m/Y H:i:s",strtotime($reg->data))?></span>
		<?=$server?>
        </span>
    </div>
    <?
}




if ($op=='savecomentario') {
	$origem = aspas($p1);
	$texto = aspas($p2);
	$iduser = val($p3);
	if ($texto) {
	sqli("insert into comentario set origem='$origem', comentario='$texto', iduser='$iduser'");
	$vid = $con_essa->insert_id;
	
	$reg = objetoi("select comentario.*, jos_users.name, jos_users.username from comentario,jos_users where comentario.id = '$vid' and  jos_users.id=comentario.iduser");
    $server = "<a href=\"javascript:server('editcoment','coment$reg->id','$reg->id','$iduser')\"><img src=images/tools/note_edit.png width=16></a>";
	} else {
		echo "Não salvei comentário.<p>";
	}
?>
    Comente:<br>
    <textarea  name=comentario rows=5 cols=40></textarea>
    <a href="javascript:server('savecomentario','spanlocal2','<?=$origem?>',form1.comentario.value,'<?=$iduser?>')" class='btn btn-sm btn-success'>Salvar</a>

    <br>	
	<br>
    <div style='margin:5px; padding:5px; font-size:0.9em;'>
	<span id=coment<?=$reg->id?>>
		<?=$reg->comentario?>
        <br><b><?=$reg->username?></b> <span class=gray><?=$reg->name?></span>
        <span class=obs><?=date("d/m/Y H:i:s",strtotime($reg->data))?></span>
		<?=$server?>
        </span>
    </div>
	<?
}


if ($op=="savecontato") {
    $cp = explode("|", $p1);
    $idempresa = $cp[0];
    $datalig = aspas($cp[1]);
    $faleicom = aspas($cp[2]);
    $cargo = aspas($cp[3]);
    $obs = aspas($cp[4]);
    $user = aspas($cp[5]);
    $acesso = aspas($cp[6]);

    if ($cargo or $faleicom or $obs) {
        sqli("insert into empresacontato (idempresa, datalig, faleicom, cargo, obs, usuario) values ('$idempresa','$datalig','$faleicom','$cargo','$obs','$user')");
    }

    $op = 'showcontatos';
    $p1 = $idempresa;
}

if ($op == 'delcontato') {
	$idempresa = $p1;
	$vid = $p2;
	$user = $p3;
	$acesso = $p4;
	sqli("delete from empresacontato where id = '$vid' and idempresa='$idempresa'");
	$op = 'showcontatos';
}

if ($op == 'showcontatos') {
    $idempresa=$p1;	

    $res2 = sqli("select * from empresacontato where idempresa = '$idempresa' order by datalig");

    while ($reg2 = $res2->fetch_object()) {
        echo "<p class='mb-1'>** " . dtoc($reg2->datalig) . " ** - " . ucfirst($reg2->usuario) . "</p>";
        echo "$reg2->faleicom --> $reg2->cargo";

        if ($user == $reg2->usuario or $acesso >= $ac_super) {
            echo " <a href=javascript:void(0) title='Excluir contato' onclick=\"server('delcontato','spancontato','$idempresa','$reg2->id','$user','$acesso')\"><img src=images/apagar.png></a>";
        }
        
        echo "<pre style='white-space: break-spaces'>$reg2->obs</pre><hr class='mt-0 anie-bottom-hr'>";
    }	
}




if ($op=='provaresp') {
	$idestudante=$p1;
	$idprova = $p2;
	$numero = $p3;
	$cod = $p4;
	$resposta = $p5;
	$cod2 = md5("sh5$idestudante");
	if ($cod2<>$cod) { 
		echo "<span class=green>A resposta não pôde ser salva, houve problemas na identificação do candidato</span>";
	} else {
		$resp = objetoi("select * from prova_respostas where idestudante = '$idestudante' and idprova = '$idprova' and numero = '$numero'");
		if ($resp->id) {
			sqli("update prova_respostas set resposta='$resposta' where id = '$resp->id'");
		} else {
			sqli("insert into prova_respostas set idprova='$idprova', idestudante='$idestudante', resposta='$resposta', numero='$numero'");
		}
		echo "<span class=green>Resposta '$resposta' registrada.</span>";
		
	}
}


if ($op=='desejado') {
	$idcurso = $p1;
	$desejado = $p2;
	$cursos = explode(',',$desejado);
	if (!in_array($idcurso,$cursos)) $cursos[] = $idcurso;
	$desejado='';
	$virgula='';
	$max = 25;
	//echo "<select name=cursodesejado style='width:250px;' size=10>";
	echo "<select name=cursodesejado style='width:400;' size=6  ondblclick=server(\"indesejado\",\"spandesejado\",this.value,document.getElementById('listacurso').value)>";
		
	foreach ($cursos as $curso) {
		if (!$curso) continue;
		
		$desejado.="$virgula".strzero($curso,4);
		$cu = objetoi("select curso from curso where id = '$curso'");
		echo "<option value='$curso'>". ($cu->curso) ."</option>";
		
		$virgula=',';
		$i++;
		if ($i>=$max) break;
		
	}
	
	echo "</select>";
	//echo "<br>";
	echo "<input type=hidden name=listacurso id=listacurso value='$desejado'>";
	
}




if ($op=='indesejado') {
	// retirar o curso clicado
	$idcurso = $p1;
	$desejado = $p2;
	$cursos = explode(',',$desejado);

	$desejado='';
	$virgula='';
	$max = 6;
	//echo "<select name=cursodesejado style='width:250px;' size=10>";
	echo "<select name=cursodesejado style='width:230px;' size=6 ondblclick=server(\"indesejado\",\"spandesejado\",this.value,document.getElementById('listacurso').value)>";
					
	foreach ($cursos as $curso) {
		if (!$curso) continue;
		if ($curso==$idcurso) continue; // faz a exclusao
		
		$desejado.="$virgula$curso";
		$cu = objetoi("select curso from curso where id = '$curso'");
		echo "<option value='$curso'>". ($cu->curso) ."</option>";
		
		$virgula=',';
		$i++;
		if ($i>=$max) break;
		
	}
	
	echo "</select>";
	//echo "<br>";
	echo "<input type=hidden name=listacurso id=listacurso value='$desejado'>";
	
}






if ($op=='showModal') {
	
	echo $p1;

}






if ($op=="avisos") {
	$iduser=$p1;
	include("avisos.php");
	
}








if ($op=="bloqueioFolha") {
	$referencia = $p1;
	$idempresa = $p2;
	$bloquear = $p3;
	$idsecretaria = $p4;
	
	if ($bloquear) {
		$bl = objetoi("select id from bloqueio_folha where idempresa='$idempresa' and idsecretaria='$idsecretaria' and referencia='$referencia'");
		if (!$bl->id) {
			sqli("insert into bloqueio_folha set referencia = '$referencia', idempresa = '$idempresa', bloqueado = 1, idsecretaria='$idsecretaria'");
		}
		echo "<span class=red>Bloqueio registrado</span>";
	} else {
		sqli("delete from bloqueio_folha where idempresa = '$idempresa' and referencia = '$referencia' and idsecretaria='$idsecretaria'");
		echo "<span class=green>Desbloqueio registrado</span>";
	}
	echo ".";

}




if ($op=='telemarketing') {
	$vid = $p1;
	$estudante = objetoi("select nome from estudante where id = '$vid'");
	?>
	<div style="width:450px; padding:10px;">
	<h3>Registro de Telemarketing</h3>
	<table border=0>
	
		<tr>
			<td align=right>Data da ligação: </td>
			<td><b><?=date("d/m/Y")?></b></td>
		</tr>
		
		<tr>
			<td align=right>Nome: </td>
			<td><b><?=($estudante->nome)?></b></td>
		</tr>
		
			<tr>

			<td align=right>Como chegou até aqui: </td>	
			<td>
			<?
				$mi = sqli("select * from midia ");
				echo "<select name=midia $ro>";
				echo "<option value=''></option>";
				while ($midi = $mi->fetch_object()) {
					$midia = ($midi->midia);
					?>
					<option value="<?=$midia?>" ><?=$midia?></option>
					<?
				}
				?>
				</select>

			</td>
			
			
		</tr>	
		
		
			<tr>

			<td align=right>Resultado: </td>	
			<td>
			<?
				showResultado("");
			?>
				</select>

			</td>
			
			
		</tr>	
		
		<tr>
	<td align=right valign=top>Descreva como foi o contato <? echo novo("26/09/2022"); ?>
	</td>
	<td>

		<textarea name=obs rows=10 cols=50></textarea>


	</td>
</tr>
		
		
		
	</table>
	<br><br>
	<input type=submit name=op2 class='btn btn-sm btn-success' value="Salvar Telemarketing">
	<input type=hidden name=op value="estudante">
	</div>
	<?
	
}








if ($op=='showquadro') {
	// quadro dos estagio feitos por p1 da empresa p2
	quadroEstagios($p1, $p2);
}







if ($op=='painelFrase') {
	?>
				<font size=+4>
				<?
				$frases = file_get_contents('../frases.txt');
				$aFrase = explode(';', $frases);
				$maximo = count($aFrase)-1;
				$sort = mt_rand(0, $maximo);
				//echo $sort ;
				?>
				<br>
 				<marquee loop=5><?=$aFrase[$sort]?></marquee>
 				</font>	
 				<?
}




if ($op=="chamadoPainelUltimos") {

					
					// ultimos nomes
					
					
					$res = sqli("select * from autoatend where sala<>'' order by chamado desc limit 0,5");
					?>
					<br>
					<table class="ultimosTable">
						<tr>
                            <th>Chamado</th>
                            <th>Sala</th>
                        </tr>
						<?
						while ($reg = $res->fetch_object()) {
							?>
							<tr>
								<td><font size="+2"><?=$reg->nome?></font></td>
								<td><font size="+2"><?=$reg->sala?></font></td>
							</tr>
							<?
						}
						?>
					</table>
				<br><br>	
				<?
}




if ($op=="chamadoPainel") {
				$atend = objetoi("select * from autoatend where sala<>'' order by chamado desc limit 1");
				if ($atend->id and !$atend->avisado) {
					
				// tocar o som
				//$arqSom='audio/campainha2.wav';
				//	echo "Som!";
					/*
				?>
				<script type="text/javascript">
					
					audio.play();
				</script>
				<?
			*/
				sqli("update autoatend set avisado=1 where id = '$atend->id'");
				
				}
					$aNome = explode(' ', $atend->nome);
					$partNome = $aNome[0] . ' ' . $aNome[1];
					$os = array("DE", "DA", "DO", "DAS","DOS"); 
					if (in_array($aNome[1], $os)) { 
						$partNome .= " ".$aNome[2];
					}


				?>
				<font color="#517C9E" size=+3><h1><b><?=$partNome?></b></h1></font>
				<font color="#69C33D" size=+3><h1><b><?=$atend->sala?></b></h1></font>

<?
}



//server('addHist','$spanId','empresa','$reg->idempresa',document.getElementById($textId).value,'$username') 
if ($op=='addHist') {
	$tabela=$p1;
	$id = $p2;
	$obs = $p3;
	$username = $p4;
	$iduser=$p5;

	$obs = trim($obs);
	if ($obs) {
		$obs = "
$obs
$username " . date("d/m/Y H:i");
		
		if ($tabela=='estagiario') {
			sqli("update estagiario set obs=concat(obs,'$obs') where id = '$id'");
			$reg = objetoi("select nome from estagiario where id = '$id'");
			log2($iduser,"Salvo obs no historico do estagiario $reg->nome");
		} elseif ($tabela=='vaga') {
		
			sqli("update vagas set beneficios=concat(beneficios,'$obs') where id = '$id'");
		
			//$reg = objetoi("select setor from vagas where id = '$id'");
			log2($iduser,"Salvo historico da vaga $id");
		
		
		} else {
			/*
			falta programar empresa
			sqli("update empresa set observacao=concat(observacao,'$obs') where id = '$id'");
		
			$reg = objetoi("select fantasia from empresa where id = '$id'");
			log2($iduser,"Salvo obs no historico da empresa $reg->nome");
			*/
		}
		echo "<font color=red><br>Salvo!</font>";
	}
}




if ($op=='autoCheckCpf') {
	$cpf=$p1;
	echo '<div class="transbox"><p>'; 
	if (cpf($cpf)) {
		$estu = objetoi("select nome,id from estudante where cpf = '$cpf'");
		if (!$estu->id) {
			echo "<font color=red>CPF '$cpf' não encontrado em nosso banco de dados.</font>";
		} else {
			autoAtendOpcoes(($estu->nome), $cpf,$estu->id);

		}

	} else {
		echo  "<h2><font color=red>CPF inválido, por favor digite novamente</font></h2>";
	}
	echo "</p></div>";

}

if ($op=='autoCheckNome') {
	$nome = caracEspeciais($p1);
	//$nome=($p1);
	echo '<div class="transbox"><p>'; 
	if ($nome) {

			autoAtendOpcoes($nome, '','');

	} else {
		echo  "<h2><font color=red>Nome Inválido</font></h2>";
	}
	echo "</p></div>";

}



if ($op=='changeSala') {
	$sala = $p1;
	$username = $p2;
	writeconfig("sala $username",$sala);
	echo "Sala de $username: $sala";
}




if ($op=='autoAtendOp') {
	//$opcao = ($p1);
	$opcao = ($p1);
	$nome= $p2;
	$cpf = $p3;
	$id = $p4;

	$opcao		= aspas($opcao);  
	$nome		= aspas($nome);  
	$idestudante		= val($id);  
	$sala		= aspas($sala);  
	$chegada = date("Y-m-d H:i:s");
	$chamado = ''; 
	$sala = '';

 	sqli("insert into autoatend ( opcao, nome, idestudante, chegada, chamado, sala) 
	values ( '$opcao', '$nome', '$idestudante', '$chegada', '$chamado', '$sala')");

	$insert_id = $con_essa->insert_id;
		
	$now = time(); sqli("update estudante set acessado = '$now' where id = '$idestudante'");
	

 	$textoAviso = "$nome aguardando atendimento para $opcao";

		$destinatarios = explode(',',readconfig('painelEnviarAviso'));

				foreach($destinatarios as $destinatario) {
					$destinatario=trim($destinatario);
					if (!$destinatario) continue;
					sqli("insert into aviso set destinatario='$destinatario',texto='$textoAviso'");
					
				}


	$op='comoChegou';

	
}



if ($op=='comoChegou') {
echo '<div class="transbox"><p>';
echo "<h3><font color=black><b>$nome.</b></font> Como chegou até aqui?</h3>";
echo "<div style='min-width:350px; text-align:left; display: inline-block'>";

		
				$mi = sqli("select * from midia ");

				while ($midi = $mi->fetch_object()) {
					
					echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('finishAtend','corpo',$insert_id,'$midi->midia','$nome')\">$midi->midia</a>";
				}
				
echo "</div>";
echo "<p></p>";
echo "</div>";
}	
	
	
if ($op=='finishAtend') {

$vid = val($p1);
$midia = aspas($p2);
$nome = $p3;

if ($vid) {
	sqli("update autoatend set midia = '$midia' where id = '$vid'");
	
} else {
	echo "Id do atendimento não identificado<br>";
}
	
echo '<div class="transbox"><p>'; 
	echo "<h2>$nome</h2>";
	echo "<font color=green><h2>Aguarde seu nome ser chamado no painel</h2></font>";
	echo "<a href=?ari=1><font color=red><h2>Clique aqui para concluir</h2></font></a>";
echo "</p></div>";	
}







function autoAtendOpcoes($nome, $cpf, $id) {
echo "<h3><font color=black><b>$nome.</b></font> Escolha uma opção abaixo:</h3>";
echo "<div style='min-width:350px; text-align:left; display: inline-block'>";


$arq2 = "autoatend.txt";
$texto = file_get_contents($arq2);
$arrOp = explode(";",$texto);
sort($arrOp);

$resultEncontrado = false;
foreach($arrOp as $opcaoS) { 
	$opcaoS = trim($opcaoS);
	if (!$opcaoS) continue;
	echo "<a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','$opcaoS','$nome','$cpf','$id')\">$opcaoS</a><br>";

}  


/*
if ($id) {
	echo "<a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Verificar Vaga','$nome','$cpf','$id')\">Verificar Vaga</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Emissão de Contrato','$nome','$cpf','$id')\">Emissão de Contrato</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Entrega de Documentos','$nome','$cpf','$id')\">Entrega de Documentos</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Financeiro','$nome','$cpf','$id')\">Financeiro</a>";
	//echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Processo Seletivo','$nome','$cpf','$id')\">Processo Seletivo</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Recisão de Contrato','$nome','$cpf','$id')\">Recisão de Contrato</a>";
} else {
	echo "<a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Verificar Vaga','$nome','$cpf','$id')\">Verificar Vaga</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Emissão de Contrato','$nome','$cpf','$id')\">Emissão de Contrato</a>";	
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Cadastro novo','$nome','$cpf','$id')\">Cadastro novo</a>";
	echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Entrega de Documentos','$nome','$cpf','$id')\">Entrega de Documentos</a>";
	//echo "<br><a href=javascript:void(0) class='opcoesAtend' onClick=\"wait(); server('autoAtendOp','corpo','Processo Seletivo','$nome','$cpf','$id')\">Processo Seletivo</a>";
}
*/


echo "</div>";
}




if ($op=='checkdoc') {
	$cpf=$p1;
	$vid=$p2;
	if (!$vid) {
	if (cpf($cpf)) {
	$alu =objetoi("select id from estudante where cpf = '$cpf'");
	if ($alu->id) {
		mensa("Já existe um cadastro no nosso banco de dados com este CPF. Nova inclusão não permitida. <a href=index.php?option=com_jumi&fileid=3&op=config&op2=contato&Itemid=8>Entre em contato com $siglaEmpresa</a>",1);
		$ok= 0;
	}
	}
}
}




if ($op=='seegrupo') {
	$idgrupo = $p1;
	
	$res = sqli("select curso.curso from curso, grupocurso2 where grupocurso2.idgrupo='$idgrupo' and curso.id = grupocurso2.idcurso order by curso.curso");
	while ($reg = $res->fetch_object()) {
		echo "<br>$reg->curso";

	}
	echo "<br><a href=javascript:void(0) onclick=\"server('close','span$idgrupo',$idgrupo)\">Esconder.</a>";
}





if ($op=='seecidades') {
	$idregiao = $p1;
	
	$res = sqli("select cidade,uf from municipio, regiaogrupo where regiaogrupo.idregiao='$idregiao' and municipio.id = regiaogrupo.idmunicipio order by municipio.cidade");
	while ($reg = $res->fetch_object()) {
		echo "<br>$reg->cidade($reg->uf)";

	}
	echo "<br><a href=javascript:void(0) onclick=\"server('close','span$idregiao',$idregiao)\">Esconder.</a>";
}





if ($op=='close') {
	echo ' ';
}




if ($op=='pscompareceu') {
	//echo "$p1 $p2";
	
	if ($p2=='true') {
			sqli("update processoaluno set compareceu='1' where id = '$p1'");
	} else {
			sqli("update processoaluno set compareceu='' where id = '$p1'");
		
	}
	
}



if ($op=='setregiao') {
	//echo "$p1 $p2";
	$idregiao=$p1;
	$idmunicipio=$p2;

	
	if ($p3=='true') {
		sqli("insert into regiaogrupo set idregiao='$idregiao', idmunicipio='$idmunicipio'");
		echo "<br><br>Inserido na região.</br>";
	} else {
		sqli("delete from regiaogrupo where idregiao='$idregiao' and idmunicipio='$idmunicipio'");
		echo "<br><b>Deletado da região.</b>";
		
	}
	
}


if ($op=='setgrupo') {
	//echo "$p1 $p2";
	$idgrupo=$p1;
	$idcurso=$p2;

	
	if ($p3=='true') {
		sqli("insert into grupocurso2 set idgrupo='$idgrupo', idcurso='$idcurso'");
		echo " <b>Inserido no grupo.</b>";
	} else {
		sqli("delete from grupocurso2 where idgrupo='$idgrupo' and idcurso='$idcurso'");
		echo " <b>Deletado do grupo.</b>";
		
	}
	
}



if ($op=='libaces') {
	
	$idregistro = $p1;
	$idestagiario = $p2;
	include("liberarAcessoEstudante.php");
}



if ($op=='selectVaga') {
	if ($p1=="Encaminhamento") {
		?>
	<select name=idvaga >
		<option value=0>Selecione a vaga</option>
		<? 
		$ieRes = sqli("select setor, nomeempresa, id from vagas where ativocontrato='o' and concnpj order by nomeempresa,setor");
		//$ieRes = sqli("select setor, nomeempresa, id from vagas, empresa where concnpj=empresa.cnpj and  ativocontrato='o' order by nomeempresa,setor");
		
		while ($ie = $ieRes->fetch_object()) {
			
			$ie->nomeempresa = substr($ie->nomeempresa,0,18);
			echo ("<option value=$ie->id>$ie->nomeempresa - $ie->setor ($ie->id)</option>");
			
		}
		?>
		</select>	
	<?
	} else {
		echo '';
	}
}




if ($op=='savediastrab') {
$id=$p1;
$dias=$p2;
sqli("replace diastrabalhados set id = '$id', dias = '$dias'");
echo "Salvo $dias para id $id";


}







if ($op=='seeHist') {
	$tabela=$p1;
	$id = $p2;
	$spanId = $p3;
	if ($tabela=='estagiario') {
		$reg = objetoi("select obs from estagiario where id = '$id'");
	} else if ($tabela=='vaga') {
		$reg = objetoi("select beneficios as obs from vagas where id = '$id'");
	} else {
		// empresa falta programar
	}
	if ($id==-1) { echo ''; } else {
		echo "<div style='border-width:1px; border-style:solid; background-color:silver; padding:5px;'>";
		echo formatbr(($reg->obs));
	}
	echo "</div>";
	if ($id>0)	echo "<input type=button name=hidde value='Ocultar' onclick=\"server('seeHist','$spanId','$p1','-1') \">";
	
}



if ($op=='saveprotocol') {
	$descricao = $p1;
	$idempresa = $p2;
	$marcado = $p3;
	//echo "Em construção (Silvio programando) '$descricao' '$idempresa' '$marcado'";
	
	if ($marcado) {
		sqli("insert into protocolo set descricao='$descricao', idempresa='$idempresa'");
		
	} else {
		sqli("delete from protocolo where idempresa='$idempresa' and descricao='$descricao'");
		

	}
	
}







if ($op=='recuperaboleto') {
	$vid = val($p1);
	$iduser = val($p2);
	
	
	sqli("update boleto set lixeira = '0' where id='$vid'");
	log2($iduser,"Resgatado boleto $vid da lixeira");
	echo "<h3><font color=red>Resgatado boleto $vid da lixeira</font></h3>";
	
}



if ($op=='recuperaTCE') {
	$vid = val($p1);
	$iduser = val($p2);
	
	
	sqli("update jos_content set lixeira = '0' where id='$vid'");
	log2($iduser,"Resgatado TCE $vid da lixeira");
	echo "<h3><font color=red>Resgatado TCE $vid da lixeira</font></h3>";
	
}









if ($op === "changeRegime") {
    $reg = new stdClass();
    $reg->regime = $p1;
    $reg->ano = $p2;
    $reg->semestre = $p3;
    $reg->modulo = $p4;

	if ($reg->regime == "a") { ?>
        <label class="form-label">Ano</label>

        <input type="hidden" name="modulo" value="<?= $reg->modulo ?>">
        <input type="hidden" name="semestre" value="<?= $reg->semestre ?>">

        <select name="ano" class="form-select">
            <option value="0">Selecione</option>
			<option value="1" <? if ($reg->ano == "1") { echo "selected"; } ?>>1º ano</option>
			<option value="2" <? if ($reg->ano == "2") { echo "selected"; } ?>>2º ano</option>
			<option value="3" <? if ($reg->ano == "3") { echo "selected"; } ?>>3º ano</option>
			<option value="4" <? if ($reg->ano == "4") { echo "selected"; } ?>>4º ano</option>
			<option value="5" <? if ($reg->ano == "5") { echo "selected"; } ?>>5º ano</option>
			<option value="8" <? if ($reg->ano == "8") { echo "selected"; } ?>>8º ano</option>
			<option value="9" <? if ($reg->ano == "9") { echo "selected"; } ?>>9º ano</option>
		</select>
	<? } ?>

	<? if ($reg->regime=="s") { ?>
		<label class="form-label">Semestre</label>

		<input type="hidden" name="modulo" value="<?= $reg->modulo ?>">
		<input type="hidden" name="ano" value="<?= $reg->ano ?>">

		<select name="semestre" class="form-select">
		    <option value="0">Selecione</option>
			<option value="1" <? if ($reg->semestre == "1") { echo "selected"; } ?>>1º semestre</option>
			<option value="2" <? if ($reg->semestre == "2") { echo "selected"; } ?>>2º semestre</option>
			<option value="3" <? if ($reg->semestre == "3") { echo "selected"; } ?>>3º semestre</option>
			<option value="4" <? if ($reg->semestre == "4") { echo "selected"; } ?>>4º semestre</option>
			<option value="5" <? if ($reg->semestre == "5") { echo "selected"; } ?>>5º semestre</option>
			<option value="6" <? if ($reg->semestre == "6") { echo "selected"; } ?>>6º semestre</option>
			<option value="7" <? if ($reg->semestre == "7") { echo "selected"; } ?>>7º semestre</option>
			<option value="8" <? if ($reg->semestre == "8") { echo "selected"; } ?>>8º semestre</option>
			<option value="9" <? if ($reg->semestre == "9") { echo "selected"; } ?>>9º semestre</option>
			<option value="10" <? if ($reg->semestre == "10") { echo "selected"; } ?>>10º semestre</option>
			<option value="11" <? if ($reg->semestre == "11") { echo "selected"; } ?>>11º semestre</option>
			<option value="12" <? if ($reg->semestre == "12") { echo "selected"; } ?>>12º semestre</option>
		</select>
	<? } 

    if ($reg->regime=="m") { ?>
		<input type="hidden" name="semestre" value="<?= $reg->semestre ?>">
		<input type="hidden" name="ano" value="<?= $reg->ano ?>">
		<label class="form-label">Módulo</label>
        <input type="number" class="form-control" value="<?= $reg->modulo ?>" min=0 max=10 size=3>
	<? } 
}




if ($op == "cnpjOUcpf") {
	$tipoDoc = $p1;
	$doc = $p2;

	?>
		<input type="text" name="cnpj" value="<?= $doc ?>" class="form-control" maxlength="18" onkeypress="return digitos(event, this);" onkeyup="Mascara('<?= $tipoDoc ?>', this, event);">
        <input type=hidden name=tipoDoc value='<?= $tipoDoc ?>'>
	<?
}



if ($op=='baixarboleto') {
$boleto=$p1;
$pgto = $p2;
 sqli("update boleto set recebido = 'o' , datapgto='$pgto' where id = '$boleto'");
 echo "<font color=blue>Registro baixado.</font>";
 
 $bole = objetoi("select referencia from boleto where id = '$boleto'");
	// set as recalc
	sqli("update caixaalunos set recalcular='1' where referencia = '$bole->referencia'  ");
	
	leManutFolha($boleto);
	
 
}


if ($op=='leituraManutFolha') {
	// depois que estiver pronto e testado , nós vamos chamar esta . Rotina em baixar boleto . 
	
	$boleto = $p1;
	//$simulacao = $p3;
	//echo "<h3>Leitura e manutenção de folha</h3>";
	leManutFolha($boleto,1);
}



if ($op=="caixaDesc_aprendiz") {
	$p1 = ($p1);
		sqli("update caixa_aprendiz set descricao='$p1' where id = '$p2'");
		
}



if ($op=="caixa2Desc_aprendiz") {
	$p1 = ($p1);
		sqli("update caixareceita2_aprendiz set descricao='$p1' where id = '$p2'");
		
}

if ($op=="caixaDesc") {
$p1 = ($p1);
	sqli("update caixa set descricao='$p1' where id = '$p2'");
	
}


if ($op=="caixaBco_aprendiz") {
	$p1 = ($p1);
		sqli("update caixa_aprendiz set bco='$p1' where id = '$p2'");
		
}

if ($op=="caixaBco") {
$p1 = ($p1);
	sqli("update caixa set bco='$p1' where id = '$p2'");
	
}


if ($op=="caixaGBco_aprendiz") {
	$p1 = ($p1);
		sqli("update caixaano_aprendiz set bco='$p1' where id = '$p2'");
		
	}

if ($op=="caixaGBco") {
$p1 = ($p1);
	sqli("update caixaano set bco='$p1' where id = '$p2'");
	
}

if ($op=="caixaBcoA_aprendiz") {
	$p1 = ($p1);
		sqli("update caixaadiantamento_aprendiz set bco='$p1' where id = '$p2'");
		//echo "update caixaadiantamento set bco='$p1' where id = '$p2'";
		
	}

if ($op=="caixaBcoA") {
$p1 = ($p1);
	sqli("update caixaadiantamento set bco='$p1' where id = '$p2'");
	//echo "update caixaadiantamento set bco='$p1' where id = '$p2'";
	
}

if ($op=="caixasaldoconta_aprendiz") {
	$p1 = val($p1);
	
		sqli("update caixasaldoconta_aprendiz set saldoanterior='$p1' where id = '$p2'");
		
		
	}
if ($op=="caixasaldoconta") {
$p1 = val($p1);

	sqli("update caixasaldoconta set saldoanterior='$p1' where id = '$p2'");
	
	
}



if ($op=="caixaDescAno_aprendiz") {
	$p1 = ($p1);
		sqli("update caixaano_aprendiz set descricao='$p1' where id = '$p2'");
		
	}
	
if ($op=="caixaDescAno") {
$p1 = ($p1);
	sqli("update caixaano set descricao='$p1' where id = '$p2'");
	
}


if ($op=="caixaValor_aprendiz") {
	$p1=val($p1);
	sqli("update caixa_aprendiz set valor='$p1' where id = '$p2'");

}


if ($op=="caixa2Valor_aprendiz") {
	$p1=val($p1);
	sqli("update caixareceita2_aprendiz set valor='$p1' where id = '$p2'");

}

if ($op=="caixaValor") {
	$p1=val($p1);
	sqli("update caixa set valor='$p1' where id = '$p2'");

}


if ($op=="caixaValorAno_aprendiz") {
	$p1=val($p1);
	sqli("update caixaano_aprendiz set valor='$p1' where id = '$p2'");

}
if ($op=="caixaValorAno") {
	$p1=val($p1);
	sqli("update caixaano set valor='$p1' where id = '$p2'");

}

if ($op=="caixaData_aprendiz") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixa_aprendiz set data='$p1' where id = '$p2'");

}

if ($op=="caixa2Data_aprendiz") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixareceita2_aprendiz set data='$p1' where id = '$p2'");

}




if ($op=="caixaData") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixa set data='$p1' where id = '$p2'");

}

if ($op=="caixaDataAno_aprendiz") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixaano_aprendiz set data='$p1' where id = '$p2'");

}
if ($op=="caixaDataAno") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixaano set data='$p1' where id = '$p2'");

}

if ($op=="caixaValorA_aprendiz") {
	$p1=val($p1);
	sqli("update caixaadiantamento_aprendiz set valor='$p1' where id = '$p2'");

}




if ($op=="caixaValorA") {
	$p1=val($p1);
	sqli("update caixaadiantamento set valor='$p1' where id = '$p2'");

}

if ($op=="caixaDataA") {
	$p1=($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixaadiantamento set data='$p1' where id = '$p2'");

}

if ($op=="caixaObs_aprendiz") {
	$p1=aspas($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixaadiantamento_aprendiz set obs='$p1' where id = '$p2'");

}

if ($op=="caixaObs") {
	$p1=aspas($p1);
	//$mes = date("Ym",strtotime($p1)); NAO PODE MUDAR O MÊS POIS JÁ TEM O LIMITE DE REGISTROS OCUPADO NO OUTRO MES
	sqli("update caixaadiantamento set obs='$p1' where id = '$p2'");

}


if ($op=='setRemessa') {
	if (!$p1) {
		sqli("update boleto set remessa ='-2' where id = '$p2'");
		echo "Boleto $p2 -> não enviar remessa.";
	} else {
		sqli("update boleto set remessa ='0' where id = '$p2'");
		echo "Boleto $p2 -> enviar remessa.";
	
	}
}


if ($op === 'getCidade' or $op === 'getMunicipioNascimento') {
	$selectName = strtolower(str_replace("get", "", $op));
	$resMun = sqli("select * from municipio where uf = '$p1' order by cidade");

	echo "<select name='$selectName' class='form-select' data-required='true'>";
	echo "<option></option>";

	while ($mun = $resMun->fetch_object()) {

		// $mun->cidade=($mun->cidade);
		echo "<option value='$mun->cidade' ";
		if (strtoupper($mun->cidade) == strtoupper($p2)) echo "selected";
		echo ">$mun->cidade</option>";
	}

	echo "</select>";
}



if ($op=="caixaSaldo") {
$ano = $p2;
$saldo = val($p1);
$cSaldoAnterior = "saldo anterior $ano";
writeconfig($cSaldoAnterior,$saldo);
}








if ($op=="delsms") {
sqli("delete from sms where id = '$p1'");
echo "";

}



if ($op=='maisativ') {
	$idsetor=$p1;
	$idcurso = $p2;
	$res = sqli("select * from atividadesetor where idsetor = '$idsetor' order by uso desc,atividade limit 4,9999");
	while ($reg = $res->fetch_object()) {
		echo "<input type=checkbox name=aatividade[] id=ati$reg->id value='$reg->id'";
		echo "<label for=ati$reg->id>";
		echo $reg->atividade;
		echo "</label>";
		echo br(1);
	}
}



if ($op=="buscaCurso-abertura") {
	$idsetor=$p1;
	$idcurso = $p2;
	if ($idcurso) {
		echo "<input type=hidden name=acurso[] value='$idcurso'>";
	} else {

		echo "<fieldset style='font-size:0.7em;'>";
		echo "<legend><b>Marque os cursos</b></legend>";
		echo "<div style='height:120px; overflow: auto;'>";
		$res = sqli("select curso.id, curso.curso from curso,setorcurso where setorcurso.idsetor = '$idsetor' and curso.id = setorcurso.idcurso order by curso.curso");
		echo "<input type=checkbox name=xcurso value='-1' onchange='todosCursos(this.checked)'>Todos os cursos";
		echo "<br>";
		while ($reg = $res->fetch_object()) {
			//$checado = in_array($reg->id,$aSetor);
			echo "<input type=checkbox name=acurso[] id=acurso value='$reg->id'>";

			echo "<label for=ati$reg->id> $reg->curso </label>";
			echo "<br>";
		}
		echo "</div>";
		echo "</fieldset>";

	}


	echo "<fieldset style='font-size:0.7em;'>";
	$res = sqli("select * from atividadesetor where idsetor = '$idsetor' order by uso desc,atividade limit 4");
	$minAtiv = $res->num_rows;
	echo "<legend><b>Selecione $minAtiv atividades compatíveis com o setor escolhido;</b></legend>";
	echo "<div style='height:120px; overflow: auto;'>";

	$i=0;
	while ($reg = $res->fetch_object()) {
		echo "<input type=checkbox name=aatividade[] id=ati$reg->id value='$reg->id'";
		echo "<label for=ati$reg->id>";
		echo $reg->atividade;
		echo "</label>";
		echo br(1);
		$i++;
	}
	echo "<input type=hidden name=minAtividade value='$i'>"; 
	echo "<span id=maisativ>";
	if ($i>3) echo "<a href=\"javascript:server('maisativ','maisativ','$p1','$p2')\" >Ver mais</a>";
	echo "</span>";

	echo "</div>";
	echo "</fieldset>";


}


if ($op=="buscaAtividadeSetor") {
	$nomesetor = $p1;
	$setor = objetoi("select id from setor where nomesetor like '$nomesetor'");
	echo fieldset(h("Atividades para este Setor",4));
	$res = sqli("select * from atividadesetor where idsetor = '$setor->id' order by atividade");
	while ($reg = $res->fetch_object()) {
		
		echo "<a href='javascript:void(0)' onclick=\"concatenaAtividade('$reg->atividade');\">";
		echo $reg->atividade;
		echo "</a>";
		echo br(1);
	}
	echo "</fieldset>";
}




if ($op=="buscaAtividade") {
	$idcurso = $p1;
	echo fieldset(h("Atividades para este curso",4));
	$res = sqli("select * from atividades where idcurso = '$idcurso' order by atividade");
	while ($reg = $res->fetch_object()) {
		
		echo "<a href='javascript:void(0)' onclick=\"concatenaAtividade('$reg->atividade');\">";
		echo $reg->atividade;
		echo "</a>";
		echo br(1);
	}
	echo "</fieldset>";
}




if ($op=="abervaga") {
	$res = sqli("select fantasia, id from empresa where fantasia like '$p1%' limit 5");
	while ($reg = $res->fetch_object()) {
		echo "<br><a href=?op=Vagas&op2=abertura&abertura=1&idempresa=$reg->id>$reg->fantasia</a>";
	}
	
}

if ($op=="nomeempRAE") {
	$res = sqli("select fantasia, id from empresa where fantasia like '$p1%' limit 5");
	while ($reg = $res->fetch_object()) {
		echo "<br><a href=?option=com_jumi&fileid=3&op=rae&op2=Editarrae&vid2=$reg->id>$reg->fantasia</a>";
	}
	
}


if ($op=='atualizaAcesso') {
	$porta = $p1;
	$nusuario = $p3;
	if ($p2) {
			$chave=1;
			sqli("replace useracesso (usuario,porta, permitido) values ('$nusuario','$porta',1)");
			echo "<span class=green>Permitido.</span>";
	} else { 
			sqli("delete from useracesso where usuario = '$nusuario' and porta = '$porta'");
			echo "<span class=green>Ok.</span>";
	}
	
/*

	$ace = objetoi("select usuario, permitido from useracesso where usuario = '$nusuario' and porta = '$porta'");
	
	if ($ace->usuario and $ace->permitido<>$chave) {
		$res = sqli("update useracesso set permitido = '$chave' where usuario = '$nusuario' and porta = '$porta'");
	}
	if (!$ace->usuario) {
		sqli("insert into useracesso (usuario, porta, permitido) values ('$nusuario','$porta','$chave')");
	}
	*/
}


if ($op=="TCEObsIE") {
	
	echo "<b>$p2</b>";
	echo "<input type=hidden name=codIE value='$p1'>";	
	echo "<br>";
	// observação da Instituição de Ensino.
	$ob = objetoi("select observacao from iensino where id = '$p1'");
	if ($ob->observacao) {
		echo "<br><b>Observações da IE</b><br>";
		echo "<font color=red>".formatbr($ob->observacao)."</font>";
	}
	
}




if ($op=="functionIncRegRecesso") {

	// VEJA EM estagiario.php $op2=="Registrar Recesso"

	// prepara os campos
	/*
	?>
	<div style="width:350px; background-color:lightskyblue;border-radius: 1em; padding:10px; border-style:solid; border-width:1px;">
	 Recesso remunerado concedido no período entre <input type=text name=dtinicio id=inicioRecesso 
		value="" size=10  maxlength=10 onkeypress="return digitos(event, this);" onkeyup="Mascara('DATA',this,event);">
	 e <input type=text name=dtfinal  id=fimRecesso
		value="" size=10  maxlength=10 onkeypress="return digitos(event, this);" onkeyup="Mascara('DATA',this,event);">	
		
	<input type=button value="Salvar" onClick="server('functionSalvarRecesso','spanRegRecesso',<?=$p1?>,<?=$p2?>,<?=$p3?>,document.getElementById('inicioRecesso').value,document.getElementById('fimRecesso').value)">
		<input type=button value="Cancelar" onClick="server('functionApresentaRecesso','spanRegRecesso',<?=$p1?>,<?=$p2?>,<?=$p3?>)">
	</div>

	<?
	*/
}


if ($op == "functionDelRegRecesso") {
	sqli("delete from recesso where id='$p4'");
	$folhaBloqueada = $p5;
	apresentarRecessoEstagiario($p1, $p2, $p3, $folhaBloqueada);
	
}

if ($op == "functionSalvarRecesso") {

	// VEJA EM estagiario.php $op2=="Salvar Recesso"

	/*
	$dti = ctod($p4);
	$dtf = ctod($p5);
	sqli("insert into recesso set idestagiario='$p1', iduser='$p2', dtinicio='$dti', dtfinal='$dtf'");
	apresentarRecessoEstagiario($p1,$p2,$p3);
*/
}


if ($op == "functionApresentaRecesso") {
	apresentarRecessoEstagiario($p1, $p2, $p3, $p4);
}




if ($op=="functionSetNaoMostrar") {
	
	if ($p1) { $ch=1;} else { $ch=0;}
	$vid = $p2;
	$referencia=$p3;
	$idempresa=$p4;
	sqli("update alureferencia set naomostrar='$ch' where idaluno = '$vid' and idempresa = '$idempresa' and referencia = '$referencia' ");
	//echo ("update alureferencia set naomostrar='$ch' where idaluno = '$vid' and idempresa = '$idempresa' and referencia = '$referencia' ");
	

}

if ($op=="functionSetExcluidoSISPAG") {
	
	if ($p1) { $ch=1;} else { $ch=0;}
	$vid = $p2;
	sqli("update estagiario set excluidoSISPAG='$ch' where id = '$vid'");
	
}



function apresentarRecessoEstagiario($p1, $p2, $p3, $p4) {
	$folhaBloqueada=$p4;
	?>
	<div style="font-size:10px;">
	<?
	$rece = sqli("select * from recesso where idestagiario='$p1' order by dtinicio");
	if ($rece->num_rows>0) {
	echo "<b><u>Recesso concedido:</u></b>";
	$gozado=0;
	while ($reg = $rece->fetch_object()) {
		$dias = round((strtotime($reg->dtfinal) - strtotime($reg->dtinicio))/86400 +1 ,0);
		echo "<br>$dias dias de ". dtoc($reg->dtinicio) . " até " . dtoc($reg->dtfinal); 
		echo " (folha:$reg->referencia";
		if (!$reg->referencia) echo "não ind.";
		echo ")";
		
		?>
		<a href=?op=esta&op2=avisorecesso&vid=<?=$reg->id?> target=_blank><img src=images/html.png width=25px title="Aviso de Recesso preenchido." ></a> 
		<? if (!$folhaBloqueada) { ?>
		<img src=images/apagar.png title="Apagar!" onclick="server('functionDelRegRecesso','spanRegRecesso',<?=$p1?>,<?=$p2?>,<?=$p3?>,<?=$reg->id?>,<?=$folhaBloqueada?>)">
		<? } ?>
		<?
		$gozado += $dias;
	}

	if ($gozado) {
		$saldoDias=$p3-$gozado;
		echo "<br><b>Concedido $gozado dias. Saldo: $saldoDias dias.</b>";
	}

	 
	echo "<br>";
	}
/* 	substitui essa por "registrar recesso"
	<img src=images/add.png width=20px title="Registrar Recesso Concedido" onclick="server('functionIncRegRecesso','spanRegRecesso',<?=$p1?>,<?=$p2?>,<?=$p3?>)">
	<a hrefimages/avisorecesso.pdf target=_blank><img src=/images/pdf.gif width=20px title="PDF: Aviso de recesso remunerado"></a>	
	
	 */
	?>

	</div>
	<?
}




if ($op=='seekcidade') {
	$cidade=$p1;
	$estado=$p2;
	$acesso=$p3;
	$mm = '';
	if ($estado) $mm.=" and uf like '$estado'";
	$cid = objetoi("select * from municipio where cidade like '$cidade' $mm");
	if (!$cid->id) {
		echo "<span class=red>Cidade/Estado não identificado.</span>";
	} else {
		echo 'Cidade OK. ';
		$sql = "select regiao.id, regiao.nomeregiao from regiao,regiaogrupo where regiaogrupo.idmunicipio='$cid->id' and regiao.id = regiaogrupo.idregiao";
		$regiao = objetoi($sql);
		if ($acesso) {

			if (!$regiao->id) {
				echo "Região não cadastrada";
			} else {
				echo "Região $regiao->nomeregiao";
			}
		}
	}
	
}




if ($op=="functionSeekSecretaria") {
?>
	<span class="form_item">
    <fieldset >
    <legend>Lotação</legend>
<?
 
$idempresa= $p2;
//echo "debug p2=$p2";
$idsupervisor = $p1;
$idsecretaria=$p3;
$se = sqli("select * from secretaria where idempresa = '$p2' order by secretaria");
if ($se->num_rows) {
	//echo "Nome da Secretaria ";

		echo "<select name=idsecretaria style='width:100%;' onchange=\"server('functionSeekSubLotacao','spanSublotacao',this.value,'$idsupervisor','$idempresa')\">";
		echo "<option value=0>Selecione</option>";
		while ($sec=$se->fetch_object()) {
			echo "<option value=$sec->id ";
				if ($idsecretaria) {
					if ($sec->id==$idsecretaria ) { echo "selected"; }
				} else {
					if ($sec->id==$reg->idsecretaria ) { echo "selected"; }
				}
			echo ">$sec->secretaria</option>";
			
		}
		echo "</select>";
		
}
?>
</fieldset>
</span>	
<?
}




if ($op=="RetidoEmpresa") {
	$fantasia = $p1;
	$empresa = objetoi("select id from empresa where fantasia like '$fantasia'");
	//echo "Silvio testando... '$fantasia' '$empresa->id'";
	?>
		<select name=referencia onchange="server('RetidoReferencia','spanAluno',this.value,'<?=$empresa->id?>')">
		<option value=''></option>
		<? $res = sqli("select DISTINCT referencia from alureferencia where alureferencia.idempresa = '$empresa->id' order by alureferencia.id desc "); 
		while ($reg2 = $res->fetch_object()) {
			echo "<option value='$reg2->referencia' ";
			if ($reg2->referencia==$reg->referencia) {echo  'selected'; }
			echo ">$reg2->referencia</option>";
		}
		?>
		</select>	
	<?

}




if ($op=="RetidoReferencia") {
	$referencia = $p1;
	$idempresa = $p2;
	
	?>

		<select name=aluno onchange="server('RetidoAluno','spanValorMotivo',this.value,'<?=$idempresa?>','<?=$referencia?>')">
		<option value=''></option>
		<?
		$sql = "select nome,id from alureferencia,estagiario where estagiario.id=alureferencia.idaluno and alureferencia.referencia = '$referencia' and alureferencia.idempresa='$idempresa' order by estagiario.nome "; 
		
		//echo $sql;
		$res = sqli($sql); 
		
		
		while ($reg2 = $res->fetch_object()) {
			echo "<option value='$reg2->nome;$reg2->id' ";
			if ($reg2->nome==$reg->aluno) { echo 'selected'; }
			echo ">$reg2->nome</option>";
		}
		?>
		</select>

<?
}

if ($op=="functionDeletePgtoRet") {
	$vid=$p1;
	$username=$p2;
	$reg = objetoi("select * from pgtoretido where id = '$vid'");
	sqli("delete from pgtoretido where id = '$vid'");
	echo "Registro de Pgto Retido excluído";
	
			$hora = date("H:i:s");
			$Hist = aspas("<font color=green>Excluido registro </font> Empresa: $reg->empresa data: ".dtoc($reg->data)." referencia: $reg->referencia valor: $reg->valor aluno: $reg->aluno motivo: $reg->motivo");
			
			// por aluno
			sqli("insert into hatu (idregistro, usuario, data, hora, atualizacao) values ('retido-$reg->idestagiario', '$username', curdate(), '$hora', '$Hist')");	

			// por banco
			sqli("insert into hatu (idregistro, usuario, data, hora, atualizacao) values ('retido-$reg->banco', '$username', curdate(), '$hora', '$Hist')");	

}


if ($op=='functionInsertPgtoRet') {
	include("insertPgtoRet.php");

}


if ($op=='pendenciaTermoAditivo') {
	$vid = $p1;
	$pendente = $p2;
	if ($pendente) {
		sqli("update termoaditivo_solicitado set pendenteentrega = 1 where id = '$vid'");
		//echo "<span class=small> Ok. Salvo como pendente.</span>";

	} else {
		sqli("update termoaditivo_solicitado set pendenteentrega = 0 where id = '$vid'");
		//echo "<span class=small> Ok. Salvo como entregue.</span>";

	}
}


if ($op=="RetidoAluno") {
$aluno = $p1;
$idempresa = $p2;
$referencia = $p3;

$reg = objetoi("select alureferencia.total-alureferencia.taxaadm as valor,pendenciacomprovante,pendenciadoc ,pendenciarg , pendenciarelatorio, pendenciadecmat,pendenciadadosbco ,pendenciatce , pendenciatermo from alureferencia,estagiario where referencia='$referencia' and alureferencia.idempresa = '$idempresa' and idaluno = estagiario.id and estagiario.nome='$aluno'");

		$pendentes = '';
			if ($reg->pendenciacomprovante) { $pendentes .= "Comprovante Residência. "; }
			if ($reg->pendenciadoc) { $pendentes .= "CPF. "; }
			if ($reg->pendenciarg) {$pendentes .= "RG. "; }
			if ($reg->pendenciadecmat) { $pendentes .= "Dec.Matrícula. "; }
			if ($reg->pendenciadadosbco) { $pendentes .= "Dados do Banco. "; }
			if ($reg->pendenciatce) { $pendentes .= "TCE. "; }
			if ($reg->pendenciatermo) {	$pendentes .= "Termo Aditivo. "; }	
			if ($reg->pendenciarelatorio) {	$pendentes .= "Relatório. "; }	

			
?>
	<table>
	<tr>
		<td align=right>Valor</td>
		<td> <input type=text name=valor value="<?=$reg->valor?>" maxlength="10" size="10" >

		</td>
	</tr><tr>
		<td align=right>Motivo</td>
		<td> <input type=text name=motivo value="<?=$pendentes?>" maxlength="40" size="40" onChange="javascript:this.value=this.value.toUpperCase();">

		</td>
	</tr>
	</table>
<?

}





if ($op == 'pedrecisao') {
	$idestagiario = $p1;
	$termino = $p2;
	$antecipada = $p3;
	$ro = $p4;

	if ($antecipada < $termino) { ?>
        <div class="card rounded-0">
            <div class="card-header">
                <h5>Motivo da Recisão Antecipada</h5>
                <span class="text-danger fw-bold">Selecione um motivo abaixo:</span>
            </div>

            <ul class="list-group list-group-flush">
                <?
                $arq2 = "recisaoped.txt";
                $texto = file_get_contents($arq2);
                $arrOp = explode(";",$texto);
                //sort($arrOp);

                foreach($arrOp as $opcaoS) { 
                    $motivo = trim($opcaoS);

                    if (!$motivo) continue;
                    $imotivo++;

                    ?>
                        <li class="list-group-item">
                            <label for='motivo<?=$imotivo?>' class="form-label"><input type="radio" name="motivorecisao" required id='motivo<?=$imotivo?>' value="<?=$motivo?>" > <?= $motivo ?></label>
                        </li>
                    <?
                }

                $imotivo++;
                ?>

                <li class="list-group-item">
                    <label for='motivo<?=$imotivo?>' class="form-label">
                        <input type="radio" name="motivorecisao" id='motivo<?=$imotivo?>' value='Outro' required> Outro, especifique:
                    </label>
                    
                    <input type="text" name="outromotivo" value='' class="form-control bg-white" maxlenght="100">
                </li>
            </ul>
			<?php
			if ($ro) {
				// somente se for readonly pois indica que a folha tá fechada e precisa esse botão pra finalizar
				?>
				<div class="card-footer">
					<input type="button" onclick="setFormAction('Registrar Pedido de Recisão')" class='btn btn-sm btn-danger btn-xs mb-1' value="Registrar Pedido de Recisão">
				</div>
				<?
			}
			?>
        </div>

	<? }
	
}



if ($op=='serverEdit') {
		
	$p = explode('|',decripta($p1));
	$tabela = $p[0];
	$nomeCampoChave = $p[1];
	$valorCampoChave = $p[2];
	$nomeCampoEditavel = $p[3];
	$valorCampoEditavel = $p[4];
	$tipo = $p[5];
	$span_id = $p[6];
	$size=10;

	if (!$tipo) $tipo='text';
	
	$type = $tipo;
	if ($type=='text' or $type=='email') {
		$size = strlen($valorCampoEditavel);

	}
	if ($tipo=='valor' and $valorCampoEditavel==0) {
		$valorCampoEditavel='';

	}

	if ($type=='valor') {
		$type='text';
		$size=10;	
	} 

	$toUpper = "onChange='javascript:this.value=this.value.toUpperCase();'";
	if ($tabela=='config') $toUpper='';
	
	$reg = objetoi("select $nomeCampoEditavel from $tabela where $nomeCampoChave = '$valorCampoChave' limit 1");
	$valorCampoEditavel = $reg->$nomeCampoEditavel;
		
	echo "<input type='$type' autofocus name='edit' $toUpper size='$size' value='$valorCampoEditavel' onblur=\"server('serverSave','$span_id','$p1',this.value)\">";
	
}



if ($op=='savelocal') {
	$arq = $p1;
	$texto = $p2;
	file_put_contents($arq,$texto);
	echo "Salvo em $arq ".date("H:i:s");
}




if ($op=='linksdiversos') {

$tbIdEstudante = $p1;
$tbEstudante = new stdClass();
$tbEstudante = objetoi("select nome,id from estudante where id = '$tbIdEstudante'");
$tbEstudante->link = "?op=estudante&op2=Estudante";
if ($tbEstudante->id) {
    $tbEstudante->link = "?op=estudante&op2=EditarEstudante&vid=$tbEstudante->id";
}

$tbIdVaga = $p2;
$tbVaga = new stdClass();
$tbVaga = objetoi("select setor as nome,id from vagas where id = '$tbIdVaga'");
$tbVaga->link = "?op=Vagas&op2=Vagas";
if ($tbVaga->id) {
    $tbVaga->link = "?op=Vagas&op2=EditarVagas&vid=$tbVaga->id";
}


$tbIdEmpresa = $p3;
$tbEmpresa = new stdClass();
$tbEmpresa = objetoi("select fantasia as nome,id from empresa where id = '$tbIdEmpresa'");
$tbEmpresa->link = "?op=empresa&op2=Empresa";
if ($tbEmpresa->id) {
    $tbEmpresa->link = "?op=empresa&op2=Editar&vid=$tbEmpresa->id";
}


$tbIdIE = $p4;
$tbIE = new stdClass();
$tbIE = objetoi("select fantasia as nome,id from iensino where id = '$tbIdIE'");
$tbIE->link = "?op=iensino&op2=iensino";
if ($tbIE->id) {
    $tbIE->link = "?op=iensino&op2=Editariensino&vid=$tbIE->id";
}


?>

<a href="<?=$tbEstudante->link?>" target=_blank>
<img src="images/estudante.png" width=15x title="<?=$tbEstudante->nome?>">

</a>


<a href="<?=$tbVaga->link?>" target=_blank>
<img src="images/vagas.png" width=15x title="<?=$tbVaga->nome?>">
</a>


<a href="<?=$tbEmpresa->link?>" target=_blank>
<img src="images/empresa.png" width=15x title="<?=$tbEmpresa->nome?>">
</a>


<a href="<?=$tbIE->link?>" target=_blank>
<img src="images/ie.png" width=15x title="<?=$tbIE->nome?>">
</a>
<?
}




if ($op=='serverSave') {
 	$p = explode('|',decripta($p1));
	
	
	$tabela = $p[0];
	$nomeCampoChave = $p[1];
	$valorCampoChave = $p[2];
	$nomeCampoEditavel = $p[3];
	$valorCampoEditavel = $p[4];
	$tipo = $p[5];
	$span_id = $p[6];
	
	$valorCampoEditavel = $p2; // novo valor
	 if ($tipo=='text') { 
	 	$valorCampoEditavel = strtoupper($valorCampoEditavel); 
	 }
	 if ($tipo=='valor') { $valorCampoEditavel = val($valorCampoEditavel); }
	 
	
	 $para = "$tabela|$nomeCampoChave|$valorCampoChave|$nomeCampoEditavel|$valorCampoEditavel|$tipo|$span_id";
	 $parametros = cripta($para);

	 
	 echo "<a href=javascript:void(0) onclick=\"server('serverEdit','$span_id','$parametros')\">";

	 if ($tipo=='date') {
	 	echo dtoc($valorCampoEditavel);
	 } else {
		 echo $valorCampoEditavel;
		 if (trim($valorCampoEditavel)==='') echo "<span class=obs>(vazio)</span>";
	 }
	 echo "</a>";
	
		sqli("update $tabela set $nomeCampoEditavel = '$valorCampoEditavel' where $nomeCampoChave = '$valorCampoChave'");
	
	
}




if ($op=='lookforvaga') {
	$seek=$p1;
	$iduParceiro = $p2;
		$mais = '';
		$mais .= " and (id = '$seek' or nomeempresa like '%$seek%' or setor like '%$seek%')";
		if ($iduParceiro) { $mais .= " and idparceiro='$iduParceiro'"; }
		$ieRes = sqli("select setor, nomeempresa, id from vagas where ativocontrato='o' and concnpj $mais order by nomeempresa,setor limit 5");
		//$ieRes = sqli("select setor, nomeempresa, id from vagas, empresa where concnpj=empresa.cnpj and  ativocontrato='o' order by nomeempresa,setor");
		echo "<b>Selecione a vaga abaixo:</b>";
		while ($ie = $ieRes->fetch_object()) {
			
			$ie->nomeempresa = substr($ie->nomeempresa,0,25);
			$xvaga = "$ie->nomeempresa - $ie->setor ($ie->id)";
			// retirar o caractere & de xvaga
			$xvaga = str_replace("&","e",$xvaga);
			echo "<br><a href='javascript:void(0)' onclick=\"server('TCEsecretaria','spanvagas','$ie->id','$xvaga')\"> $xvaga</a>";
			
		}	
		echo "<br>";
}

 




if ($op=='procestudante') {
	$seek = $p1;
	
	echo "<b>Selecione o estagiário abaixo:</b>";
	$ieRes = sqli("select nome, id from estudante where nome like '%$seek%' or cpf like '$seek%' order by id desc limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='?op=estudante&op2=Procurar&seek=$ie->id' >$ie->nome</a>";
			
		}	
		echo "<br>";
	
}



if ($op=='procempresa') {
	$seek = $p1;
	$and2 = "";
	$seekCNPJ = substr(formataCNPJ($seek),0,strlen($seek));
	if ($seekCNPJ) {
		$and2 .= " or cnpj like '$seekCNPJ%'";
	}
	
	$seekCPF = substr(formataCPF($seek),0,strlen($seek));
	if ($seekCPF) {
		$and2 .= " or cnpj like '$seekCPF%'";
	}
	

	echo "<b>Selecione a empresa abaixo:</b>";
	$ieRes = sqli("select nome, id, fantasia from empresa where fantasia like '%$seek%' or nome like '%$seek%' $and2 order by id desc limit 5");
		
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='?op=empresa&op2=Procurar&seek=$ie->id' >$ie->fantasia - $ie->nome</a>";
			
		}	
		echo "<br>";
}


if ($op=='setSetor') {
	$idcurso = $p1;
	$idsetor = $p2;
	$checado = $p3;
	echo "$idcurso $idsetor '$checado'";
	$setorcurso = objetoi("select * from setorcurso where idcurso='$idcurso' and idsetor='$idsetor'");
	if ($checado=='true') {
		if (!$setorcurso->id) sqli("insert into setorcurso set idcurso='$idcurso', idsetor='$idsetor'");
		echo "<b>incluído</b>";
	} else {
		sqli("delete from setorcurso where id = '$setorcurso->id'");
		echo "<b>excluído</b>";
	}
}



if ($op=='seekcandidato') {
	$seek = $p1;
	echo "<b>Selecione o estagiário abaixo:</b>";
	$ieRes = sqli("select nome, id from estudante where (status = '' or status='en') and nome like '%$seek%' order by nome limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='javascript:void(0)' onclick=\"server('setCandidato','spancandidato','$ie->id','$ie->nome'); document.form1.op2.disabled=false; candidatoEscolhido()\">$ie->nome</a>";
			
		}	
		echo "<br>";
		echo "<input type=hidden name=idestudante id=idestudante value=''>"; // deixa desmarcado, caso não selecionou ninguém
}


if ($op=='removecandidato')
{
	echo "<b>Estagiário removido.</b>";
	echo "<input type=hidden name=idestudante id=idestudante value=''>";
}


if ($op=='setCandidato') {
	echo "<b>$p2</b>";
	echo "<input type=hidden name=idestudante id=idestudante value='$p1'>";
	echo "<br>";
	echo " <input type=button value='Remover' class='btn btn-danger btn-xs' onclick=\"server('removecandidato','spancandidato','$vid')\">";
	$now = time(); sqli("update estudante set acessado = '$now' where id = '$p1'");


}


if ($op=='lookforempresa') {
	$seek = $p1;
	echo "<b>Selecione a empresa abaixo:</b>";
	$ieRes = sqli("select nome,fantasia, id from empresa where nome like '%$seek%' or fantasia like '%$seek%' order by nome limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='javascript:void(0)' onclick=\"server('setEmpresa','spanempresa','$ie->id','$ie->nome')\">$ie->nome - $ie->fantasia</a>";
			
		}	
		echo "<br>";
}



if ($op=='setEmpresa') {
	echo "<b>$p2</b>";
	echo "<input type=hidden name=idempresa id=idempresa value='$p1'>";
	echo "<br>";
}



if ($op=='lookforestagiario2') {
	$seek = $p1;
	echo "<b>Selecione o estagiário abaixo:</b>";
	$ieRes = sqli("select nome, id from estudante where (status = '' or status='en') and nome like '%$seek%' order by nome limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='javascript:void(0)' onclick=\"server('setEstagiario2','spanestag','$ie->id','$ie->nome')\">$ie->nome</a>";
			
		}	
		echo "<br>";
}



if ($op=='lookforestagiario') {
	$seek = $p1;
	echo "<b>Selecione o estagiário abaixo:</b>";
	$ieRes = sqli("select nome, id from estudante where (status = '' or status='en') and nome like '%$seek%' order by nome limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='javascript:void(0)' onclick=\"server('setEstagiario','spanestag','$ie->id','$ie->nome')\">$ie->nome</a>";
			
		}	
		echo "<br>";
}


// Older model
if ($op=='lookforIE') {
	echo "<b>Selecione a Instituição de Ensino abaixo:</b>";
	$ieRes = sqli("select nome, fantasia, id from iensino where status = 'a' and (nome like '%$p1%' or fantasia like '%$p1%') order by fantasia limit 5");
		while ($ie = $ieRes->fetch_object()) {
			echo "<br><a href='javascript:void(0)' onclick=\"server('TCEObsIE','spanIE','$ie->id','$ie->nome')\">$ie->fantasia - $ie->nome</a>";
		}

}


if ($op=='lookforIE2') {
    echo <<<EOT
    <div class="list-group rounded-0 shadow-sm bg-whitesmoke">
        <p class="list-group-item fw-bold mb-0 p-2 text-blue-800" style="border-bottom-width: 2pt">SELECIONE UMA INSTITUIÇÃO DE ENSINO:</p>
    EOT;

	$ieRes = sqli("select nome, fantasia, id from iensino where status = 'a' and (nome like '%$p1%' or fantasia like '%$p1%') order by fantasia limit 5");

    while ($ie = $ieRes->fetch_object()) {
        echo "<a href='javascript:void(0)' class='list-group-item list-group-item-action py-1 px-2' onclick=\"server('setIErelTCE','spanIE','$ie->id','$ie->nome','$ie->fantasia')\">$ie->fantasia - $ie->nome</a>";
    }

    echo "</div>";
}


if ($op === "setIErelTCE") {
    echo <<<EOT
        <ul class="list-group rounded-0 shadow-sm bg-whitesmoke">
            <li class="list-group-item py-1 px-2">Selecionada: <strong class='text-blue-800'>$p2</strong></li>
        </ul>

        <input type=hidden name=codIE value='$p1'>
        <input type=hidden name=instensino value='$p3'>
    EOT;
}


if ($op=='setEstagiario2') {
	echo "<b>$p2</b>";
	echo "<input type=hidden name=idestudante id=idestudante value='$p1'>";
	echo "<br>";
}


if ($op=='setEstagiario') {
	echo "<b>$p2</b>";
	echo "<input type=hidden name=codEstagiario id=codEstagiario value='$p1'>";
	echo "<br>";
}



/*

if ($op=='lookforIE3') {
	echo "<b>Selecione a Instituição de Ensino abaixo:</b>";
	$ieRes = sqli("select fantasia, id from iensino2 where (fantasia like '%$p1%') order by fantasia limit 5");
		while ($ie = $ieRes->fetch_object()) {
			
			echo "<br><a href='javascript:void(0)' onclick=\"server('setIErelTCE','spanIE','$ie->id','$ie->fantasia')\">$ie->fantasia</a>";
		}

}
*/








if ($op=="functionSeekSupervisor") {
		$idsupervisor=$p2;
		if ($p2) $reg->idsupervisor=$idsupervisor;
			?>
			<tr>
			
			<td>
			<?
			$resSu = sqli("select supervisor.nome, supervisor.id from supervisor, empresa where empresa.id='$p1' and supervisor.idempresa = empresa.id order by supervisor.nome");
			//mensa("select supervisor.nome, supervisor.id from supervisor, empresa where empresa.id='$p1' and supervisor.idempresa = empresa.id order by supervisor.nome");


			echo "<select name=idsupervisor style='width:100%;' onchange=\"server('functionSeekSecretaria','spanSecretaria',this.value,'$p1')\"
 >
			<option value=0 >Selecione o supervisor</option>";
			//onblur=\"server('functionSeekSecretaria','spanSecretaria',this.value,'$p1')\"
			while ($regsu = $resSu->fetch_object()) {
				echo "<option value=$regsu->id ";
				if ($regsu->id == $reg->idsupervisor) { echo "selected"; }
				echo ">$regsu->nome</option>";
			}
			echo "</select></td></tr>";
			
			
	
}


if ($op=="functionSeekFormacao") {

			$reg = objetoi("select formacao from supervisor where id = '$p1'");
			echo ($reg->formacao);
			
			
	
}


if ($op=="functionSeekSubLotacao") {
//$em = objetoi("select idempresa from secretaria where id = '$p1'");
$idsecretaria = $p1;
$idsupervisor = $p2; 
$idempresa = $p3;
$idsublotacao=$p4;
?>
<span class="form_item">
<fieldset>
<legend>Sublotação</legend>

<?

$se = sqli("select * from sublotacao where idempresa = '$idempresa' and (idsecretaria='$p1' or idsecretaria=0) order by nome");

if ($se->num_rows) {
	//echo "Nome da Secretaria ";

		echo "<select name=idsublotacao style='width:100%;'>";
		echo "<option value=''>Selecione</option>";
		while ($sec=$se->fetch_object()) {
			echo "<option value=$sec->id ";
				if ($idsublotacao) {
					if ($sec->id==$idsublotacao ) { echo "selected"; }
				} else {
					if ($sec->id==$reg->idsecretaria ) { echo "selected"; }
				}
			echo ">$sec->nome</option>";
			
		}
		echo "</select>";
}
?>
<div align=right style="font-size:0.8rem">
    Sublotação não está na lista? <a href=?op=empresa&op2=Editarsublotacao&idempresa=<?=$idempresa?>&idsecretaria=<?=$idsecretaria?>&idsupervisor=<?=$idsupervisor?> class='btn btn-sm btn-anie-blue' >Incluir nova</a>
   
</div>

</span>
</fieldset>
<?
}



if ($op=='visuContrato') {
	$arr = explode("|",$p1);
	$idcontrato = -1; // $arr[0];
	$codIE = $arr[1];
	$codEstagiario = $arr[2];
	$codVaga = $arr[3];
	$ddata = $arr[4];
	$vigencia1 = $arr[5];
	$vigencia2 = $arr[6];
	$pendenciadadosbco = $arr[7]=='true' ? 1 : 0;
	$pendenciacomprovante = $arr[8]=='true' ? 1 : 0;
	$assinatura = $arr[9];


	$registrar=0;
	include("f_fillcontrato3.php");
	//if ($user=='silvio') $registrar=0;
	
	// primeiro preenche o contrato para registrar informações q serão usadas nos outros relatorios  
	$aContrato = fillcontrato3($idcontrato,$codIE,$codEmpresa,$codEstagiario,$codVaga, $ddata,
	  $orientadorIE, $vigencia1, $vigencia2, $op3, $idsecretaria, $pendenciadoc,$pendenciacomprovante,$pendenciarg, $pendenciadecmat, $pendenciadadosbco, $pendenciatce,$username,$iduParceiro,$bancoEstag,$assinatura,$registrar);
	
	$contrato=$aContrato[0];

	echo "<fieldset ><legend>Pré-visualização do contrato</legend>";
	echo "<div style='width:100%; font-size:0.7rem; overflow:auto; height:400px;'>";
	echo $contrato;
	echo "</div>";
	echo "</fieldset>";

	echo '<input type=submit class="btn btn-sm btn-success" name=op2 value="Emitir TCE" >';
}


if ($op=='visuContratoAprendiz') {
	$arr = explode("|",$p1);
	$jAprendiz = new jAprendiz($arr[0]);
	//$jAprendiz->idestudante = $arr[0];
	//$jAprendiz->codVaga = $arr[1];
	$jAprendiz->data = $arr[1];
	$jAprendiz->assinatura = $arr[2];
	$jAprendiz->datainicio = $arr[3];
	$jAprendiz->diasemana = $arr[4];
	$jAprendiz->horarioinicio = $arr[5];
	$jAprendiz->horariofinal = $arr[6];
	//$jAprendiz->cargahoraria = $arr[8];
	$jAprendiz->diavencimento = $arr[7];
	$jAprendiz->idcurso = $arr[8];
	$jAprendiz->idempresa = $arr[9];

	$jAprendiz->preContrato();
	

}



if ($op=='seekvaga') {
	
	$p1 = val($p1);
	$vaga = objetoi("select setor from vagas where id = '$p1'");
	echo ($vaga->setor);
	
}




if ($op=="TCEsecretaria") {
	$idvaga= $p1;
	$xvaga = $p2;
	echo "<b>$xvaga<br></b>";
	echo "<input type=hidden name=codVaga id=codVaga value='$idvaga'>";

	//echo "<br>";
	
	$va = objetoi("select concnpj from vagas where id = '$p1'");
	$em = objetoi("select id from empresa where cnpj = '$va->concnpj'");
$se = sqli("select * from secretaria where idempresa = '$em->id' order by secretaria");
if ($se->num_rows) {
	//echo "Nome da Secretaria ";
	echo "Lotação ";
	


		echo "<select name=idsecretaria>";
		while ($sec=$se->fetch_object()) {
			echo "<option value=$sec->id ";
				if ($idsecretaria) {
					if ($sec->id==$idsecretaria ) { echo "selected"; }
				} else {
					if ($sec->id==$reg->idsecretaria ) { echo "selected"; }
				}
			echo ">$sec->secretaria</option>";
			
		}
		echo "</select>";
		//echo "<br>";
}

}





if ($op=="encaminha") {
	$res = sqli("select id, nome from estudante where nome like '$p1%' and (status='' or status='d') limit 5");
	echo "<b>Clique no nome do aluno para continuar<br></b>";
	while ($reg = $res->fetch_object()) {
		echo "<br><a href=index.php?option=com_jumi&fileid=3&op=enca&op2=encaminha&idaluno=$reg->id&idvaga=$p2&op3=insert>$reg->nome</a>";
	}

}









if ($op=="nomecad") {
	$tabela = $p1;
	$vid = $p2;
	
	$no = objetoi("select nome from $tabela where id = '$vid'");
	
	echo "$no->nome";
	
}













if ($op=="extenso") {
	if (strpos($p1,',') and strpos($p1,'.')) {
		echo "<p id=destaque>Nao use separador de milhar</p> ";
	}
	require("extenso.php");
	$var=$p1;

	$var = rtrim($var);
	$var = ltrim($var);
	$var = strtr($var,',','.');
	$var = strval($var);
	if ($var=="") {$var="0";}
	
	if ($var) {
		echo extenso($var);
	} else {
		echo "Sem valor";
	}
	
}

if ($op === "horabus") {
    include("lotacao.php");
}


if ($op == "verificarCpf") {
	list($cpf, $acesso, $vid) = [$p1, $p2, $p3];

	if (!$vid and cpf($cpf) and $p4 !== "responsavelcpf") {
		$cpfCad = sqli("SELECT id, nome FROM estudante WHERE cpf = '$p1'");

		if ($cpfCad->num_rows) {
			if ($acesso >= $ac_escritorio) {
				$cpfCad = $cpfCad->fetch_object();

				$additionalMess = "<a href='?option=com_jumi&fileid=3&op=estudante&op2=EditarEstudante2&vid={$cpfCad->id}'>Acesse os dados do estudante</a>.";
			} else {
				$additionalMess = "Não é possível continuar. <a href='?op=config&op2=contato&Itemid=8'>Entre em contato.</a>";
			}

			echo <<<EOT
            <div class='anie-alert-inline alert alert-danger mt-2'>
                <div>
                    <strong class='d-block'>CPF $cpf já cadastrado.</strong> $additionalMess
                </div>
            </div>
            EOT;
		}
	}
}

if ($op === "detalhesVaga") {
/*
	$arrId = explode(',',$id);
	$id=$arrId[0];
	$acesso=$arrId[1];
*/

	function insertDetail($title, $description) {
		return "<p class='vagaDetail'><strong>$title</strong> $description</p>";
	}

	$vaga = objetoi("SELECT vagas.*,empresa.cidade, empresa.estado, empresa.id as idempresa FROM vagas,empresa WHERE vagas.id = '$id' and concnpj=empresa.cnpj");

	$cod = insertDetail("Código da vaga:", strzero($vaga->id, 7));
	$inativo = !$vaga->ativo ? "<span class='vagaInfo vagaInativo'><strong>INATIVO</strong></span>" : '';

	if (trim($vaga->setor)) $setorEstagiado = insertDetail("Setor Estagiado:", $vaga->setor);

	if ($vaga->bolsaauxilio) $bolsaAux = "R$ ";
	$bolsaAux = trim("$bolsaAux{$vaga->bolsaauxilio} {$vaga->bolsatipo}");

	if ($vaga->auxiliotransp) $transporte = "R$ ";
	$transporte .= trim("$vaga->auxiliotransp $vaga->auxiliotipo");

	if ($transporte) $transporte = " + Auxílio transporte: $transporte";
	$bolsaAux = ($transporte or $bolsaAux) ? insertDetail("Bolsa-auxílio:", "$bolsaAux $transporte") : "";

	$horario = formatHorario($vaga->horario, $vaga->horariode, $vaga->horarioa, $vaga->horariodas, $vaga->horarioas, $vaga->horarioedas, $vaga->horarioeas);
	if ($horario) $horario = insertDetail("Horário:", $horario);

	if ($vaga->atividade) $atividade = insertDetail("Atividades do estágio:", $vaga->atividade);

	$asexo = ["a" => "Ambos os sexos", "f" => "Feminino", "m" => "Masculino"];
	if ($vaga->sexo) $sexo = insertDetail("Sexo:", $asexo[$vaga->sexo]);

	if (!$isEstudante) {
		if ($vaga->idsublotacao) {
			$sub = objetoi("select * from sublotacao where id = '$vaga->idsublotacao'");
			$lonome = $sub->nome;
			$lorua = "$sub->endereco $lonumero";
			$vaga->obs.="<br><b>Sublotação: </b>$lonome $lorua";
		}

		if ($vaga->idsecretaria) {
			$sec = objetoi("select * from secretaria where id = '$vaga->idsecretaria'");
			$lonome = $sec->secretaria;
			$vaga->obs.="<br><b>Lotação: </b>$lonome";

		}
	}


	if ($vaga->obs) $obs = insertDetail("Observações:", $vaga->obs);
	if ($vaga->cidade) $cidade = insertDetail("Cidade:", "$vaga->cidade - $vaga->estado");
	if ($vaga->conendereco) $enderecoConcedente = insertDetail("Endereço:", "$vaga->conendereco $vaga->local");
	if ($vaga->re) $re = insertDetail("RE", $vaga->re);

	$listaCurso = '';
	if ($acesso>=$ac_escritorio) {
		$cursos = explode(',',$vaga->listacurso);
		$desejado='';
		$virgula='';
		if ($cursos) {
			foreach ($cursos as $curso) {
				if (!$curso) continue;

				$cu = objetoi("select curso from curso where id = '$curso'");
				$desejado.="$virgula $cu->curso";
				$virgula=',';
			}
		}

		if ($desejado) $listaCurso = insertDetail("Curso desejado:", $desejado);
	}

	$maisInfo='';
	if (!$acesso) $maisInfo = insertDetail("Carta de encaminhamento: ","<a href=?op=estudante&op2=loginEstudante class='btn btn-sm btn-success'>Faça seu login</a>");

	if (($isEstudante and $podeEncaminhar) or $vagasParaIdAluno) {
		$vida='';
		if ($vagasParaIdAluno) {
			$link = "<a href=?op=enca&op2=encaminha&op3=insert&idaluno=$vagasParaIdAluno&idvaga=$vaga->id class='btn btn-sm btn-anie-blue'>Encaminhar</a>";
		} else {
			$link = "<a href=?op=enca&op2=candidatar&idvaga=$vaga->id$vida class='btn btn-success' role='button'>Candidatar-se</a>";
		}
		// antes de liberar encaminhamento, testar se ja tem candidato
		$candidato = objetoi("select id from controlevagas where idvaga = '$vaga->id' and candidato");
		if (!$candidato->id) $maisInfo = insertDetail(""," $link");
	}

	$candidato='';
	if ($acesso>=18 and !$isEstudante) {
		$contVaga = objetoi("select candidato from controlevagas where idvaga = '$vaga->id'");
		if ($contVaga->candidato) $candidato = insertDetail("Candidato: ",$contVaga->candidato);
	}

    $supervisor = '';
    if($vaga->idsupervisor) {
        $super = objetoi("select * from supervisor where id = '$vaga->idsupervisor'");
        $supervisor = insertDetail("Supervisor:", $super->nome);
    }

	$vagaTitle = ($acesso and $iduser) ? $vaga->nomeempresa : $vaga->setor;
	if ($ocultarEmpresa) $vagaTitle=$vaga->setor;

	echo <<<EOT
    <div class="vagas">
        <h3>$vagaTitle</h3><hr>
        $inativo
        $cod
        $setorEstagiado
        $bolsaAux
        $horario
        $atividade
        $sexo
        $re
        $listaCurso
        $obs
        $cidade
        $enderecoConcedente
        $maisInfo
        $candidato
        $supervisor
    </div>
EOT;
}


function caracEspeciais($nf) {

// substitui caracteres acentuados e outros carac especiais 
$nf = str_replace("ç", "c",$nf);
$nf = str_replace("Ç", "C",$nf);
$nf = str_replace("á", "a",$nf);
$nf = str_replace("é", "e",$nf);
$nf = str_replace("í", "i",$nf);
$nf = str_replace("ó", "o",$nf);
$nf = str_replace("ú", "u",$nf);
$nf = str_replace("Á", "A",$nf);
$nf = str_replace("É", "E",$nf);
$nf = str_replace("Í", "I",$nf);
$nf = str_replace("Ó", "O",$nf);
$nf = str_replace("Ü", "U",$nf);
$nf = str_replace("Ú", "U",$nf);
$nf = str_replace("Ã", "A",$nf);
$nf = str_replace("Õ", "O",$nf);
$nf = str_replace("À", "A",$nf);
$nf = str_replace("à", "a",$nf);
$nf = str_replace("â", "a",$nf);
$nf = str_replace("ê", "e",$nf);
$nf = str_replace("ô", "o",$nf);
$nf = str_replace("Â", "A",$nf);
$nf = str_replace("Ê", "E",$nf);
$nf = str_replace("Ô", "O",$nf);
$nf = str_replace("°", "",$nf);
$nf = str_replace("º", "",$nf);
$nf = str_replace("³", "3",$nf);
$nf = str_replace("ø", "",$nf);

return $nf;
}

leManutFolha()

function leManutFolha($nboleto,$simulacao=0) {
	// leitura de manutenção de folha
	// 25/09/2024 
	//  ao baixar um boleto em arqretorno essa função é chamada para verificar as possíveis pendencias do aluno e se é pra excluir da folha de pagamento e colocar no pagamentos retidos
	global $iduser;

	$boleto = objetoi("select referencia from boleto where id = '$nboleto'");


	// pegar a lista de alunos que compoem o boleto
	$res = sqli("select estagiario.nome, estaboleto.idestagiario from estaboleto, estagiario where estaboleto.boleto='$nboleto' and estaboleto.idestagiario = estagiario.id order by estagiario.nome");
	echo "<b>Leitura e manut. da folha de pagamento ref boleto $nboleto</b><br>";
	log2($iduser,"Leitura e manut. da folha de pagamento ref boleto $nboleto");
	while ($reg = $res->fetch_object()) {
		$est = new estagiario($reg->idestagiario,$boleto->referencia);
	//	echo $est->reg->nome;
		$est->leituraManutFolha($simulacao);
		//echo "<br>";
	}

}

?>
