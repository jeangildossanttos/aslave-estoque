<script type="text/javascript">
	$(document).ready(function(){
		var delete_produto = [];
		var total = 0
		$(".total").each(function(){
			total += Number($(this).text().replace(/[^0-9.,]/g,''));

		});
		$("#totalHeader").text("R$ "+ total);

		function reloadTableProductInput(){
			$.ajax({
				url: window.location.href,
				type: "POST",
				data: $("table"),
				success: function(data){
					$("#enteredProducts").html(" ");
					$("#enteredProducts").html($(data).find("#enteredProducts > tr"));
					$("#sum_value").html($(data).find("#sum_value"));
				},
				error: function(){
					console.log(data);
					Materialize.toast('Erro ao recarregar a tabela, atualize a pagina!', 4000);
				}
			});
		}
		function reloadTableProductInputAfterInsert(){
			$.ajax({
				url: window.location.href,
				type: "POST",
				data: $("table"),
				success: function(data){
					$("#productInput").html($(data).find("#productInput"));
					$("#sum_value").html($(data).find("#sum_value"));
				},
				error: function(){
					console.log(data);
					Materialize.toast('Erro ao recarregar a tabela, atualize a pagina!', 4000);
				}
			});
		}
		$("#productInput").on("change", ":checkbox", function(){
			if($(this).is(':checked')){
				delete_produto.push($(this).attr("id"));
				$("#add_product_input_stock_final_btn").attr("disabled", false);
				$(this).parent().parent().css("background-color", "#d9d9d9");
			}else{
				delete_produto.splice(delete_produto.indexOf($(this).attr("id")), 1);
				$(this).parent().parent().css("background-color", "white");
			}
			if(total === 0 && delete_produto.length === 0){
				$("#add_product_input_stock_final_btn").attr("disabled", true);
			}
			/*$.ajax({
				url: "<?php echo site_url('/StockController/removeProductInputStock')?>",
				type: "POST",
				data: {id_product: $(this).attr("id")},
				success: function(data){
					if(data){
						Materialize.toast("Produto retirado com sucesso", 4000);
						reloadTableProductInput();
					}else{
						Materialize.toast(data, 4000);
					}
				},
				error: function(data){
					console.log(data);
					Materialize.toast("Ocorreu algum erro ao retirar este produto", 4000);
				}
			});*/
		});

		$("#add_product_btn").click(function(){
			$('#add_product_modal').openModal();
		});

		var product_json = "<?php echo site_url('/StockController/searchProductStock')?>";
		$('#product_name').simpleSelect2Json(product_json,'id_product','name_product');

		/*$("input[name=product_name]").keyup(function(){
			if($(this).val() != ''){
				$.ajax({
					url: "<?php echo site_url('/StockController/searchProductStock')?>",
					type: "POST",
					cache: false,
					data: {name_product: $(this).val()},
					success: function(data){
						$('#loadProduct').html("");
						var obj = JSON.parse(data);
						if(obj.length>0){
							try{
								var items=[];
								$.each(obj, function(i,val){
									items.push($("<option id="+ val.id_product +">"+ val.name_product +"</option>"));
								});
								$('#loadProduct').append.apply($('#loadProduct'), items);
							}catch(e) {
								alert('Ocorreu algum erro ao carregar os Produto!');
							}
						}else{
							$('#loadProduct').html($('<span/>').text("Nenhum Produto encontrado!"));
						}
					},
					error: function(data){
						alert("Ocorreu algum erro ao carregar os Produtos");
					}
				});
			}else{
				$('#loadProduct').html(" ");
			}
		});
		$("#loadProduct").on("click", "option", function(){
			$("input[name=product_name]").val("");
			$("input[name=descript]").val("");
			$("#product").html($(this));
			$('#loadProduct').html(" ");
		});*/

		var total = 0;
		$("#generate_table_product").submit(function(e){
			e.preventDefault();
			var check = false;
			if($('#product').is(':empty')){
				Materialize.toast("Selecione algum produto", 4000);
				check = true;
			}else{
			var id = $("#product_name option:selected").val();
			$("#productInput td").each(function(i){
				if( id == $(this).attr("id")){
					check = true;
					Materialize.toast("Este produto já foi importado para a tabela", 4000);
				}
			});
			}
			if(!check){
				$("#add_product_modal").closeModal();

				var newRow = $("<tr class='productRow'>");
				var cols = "";

				cols += '<td class="tdProductId" id='+ $("#product_name option:selected").val() +'>'+ $("#product_name option:selected").text() +'</td>';
				cols += '<td class="tdProductPrice">'+'R$ '+ $("input[name=price]").val() +'</td>';
				cols += '<td class="tdProductAmount">'+ $("input[name=amount]").val() +'</td>';
				cols += '<td class="tdProductTotal">'+'R$ '+ ($("input[name=price]").val() * $("input[name=amount]").val()).toFixed(2) +'</td>';
				cols += '<td>';
				cols += '<a href="#" class="removeProduct btn red"><i class="material-icons">delete</i></a>';
				cols += '</td>';

				newRow.append(cols);
				$("#newProduct").prepend(newRow);

				total = total + ($("input[name=price]").val() * $("input[name=amount]").val());

				$("#total").html("Total: R$" +total.toFixed(2));
				$("input[name=amount]").val("");
				$("input[name=price]").val("");
				$("input[name=product_name]").val("");
				$('#loadProduct').html(" ");
				$("#product").html(" ");
				$("#add_product_input_stock_final_btn").attr("disabled", false);

			}

		});

		$("#productInput").on("click", ".removeProduct", function(e){
			e.preventDefault();
			var $this = $(this);
			var valueRemove = $this.parents("tr").find(".tdProductTotal").text().replace(/[^0-9.,]/g,'');
			total = total - valueRemove;
			$("#total").html("Total: R$ "+ total.toFixed(2));
			$(this).parents('tr').remove();
			if(total === 0 && delete_produto.length === 0 && $("#total").text() == "Total: R$ "){
				$("#add_product_input_stock_final_btn").attr("disabled", true);
			}
			});

		$("#add_product_input_stock_final_btn").click(function(e){
				e.preventDefault();
				$("#add_product_input_stock_final_btn").attr("disabled", true);

				var productsData = [];
				$(".productRow").each(function(i){
					var pData = {
						id_product: $(this).find(".tdProductId").attr("id"),
						amount:  $(this).find(".tdProductAmount").text(),
						price: Number($(this).find(".tdProductPrice").text().replace(/[^0-9.,]/g,''))
					};
					productsData.push(pData);
				});

			$.ajax({
				url: "<?php echo site_url('/StockController/insertProductsInputStock')?>",
				type: "POST",
				data: {
					id_stock: "<?= $this->uri->segment(4) ?>",
					products: JSON.stringify(productsData),
					delete_produto: delete_produto
				},
				success: function(data){
					if(!isNaN(data)){
						$("#total").html("Total: R$:");
						setTimeout(reloadTableProductInputAfterInsert(), 3000);
						$(".productRow").addClass("transition");
						Materialize.toast("Operação realizada com sucesso!", 4000);
					}else{
						Materialize.toast(data, 4000);
					}
					setTimeout(reloadTableProductInputAfterInsert(), 3000);
				},
				error: function(data){
					console.log(data);
					Materialize.toast('Erro ao adicionar um novo produto!', 4000);
				}
			});
		});
	});
</script>
<style type="text/css">
	.productRow{
		background: #33CC33;
	}
	.transition{
		background-color: white;
		-webkit-transition: background-color 3000ms linear;
		-moz-transition: background-color 3000ms linear;
		-o-transition: background-color 3000ms linear;
		-ms-transition: background-color 3000ms linear;
		transition: background-color 3000ms linear;
	}
</style>
<div class="container">
	<div class="card-panel blue-text">
		<h4>Alterar entrada [<?= $entry_data['entry'][0]['id_stock']; ?>]</h4>
		<div class="right-align">
			<a class="btn teal" href="<?=base_url('stock/entries') ?>"><i class="material-icons">input</i> Voltar</a>
			<button id="add_product_input_stock_final_btn" class="btn green" type="submit" disabled="true">Finalizar<i class="material-icons right">send</i></button>
			<!-- mas que belo ID -->
		</div>
	</div>
	<div class="row">
		<div class="card-panel">
			<dl class="dl-horizontal">
				<dt>Fornecedor</dt>
				<dd><?= $entry_data['people'][0]['name'] ?></dd>

				<dt>CPF/CNPJ</dt>
				<dd><?= $entry_data['people'][0]['cpf_cnpj'] ?></dd>

				<dt>Tipo</dt>
				<dd>
					<?php echo $entry_data['entry'][0]['input_type'] == 1 ? "Compra": "Doação"; ?>
				</dd>

				<dt>Data de Entrada</dt>
				<dd><?= date('d/m/Y', strtotime($entry_data['entry'][0]['input_date'])); ?></dd>

				<dt>Total da Nota</dt>
				<dd id="sum_value"><?='R$ '. number_format($entry_data['entry'][0]['sum_value'], 2, ',', '.');?></dd>
			</dl>
		</div>
	</div>

	<div class="card-panel">
		<div class="right-align">
			<a id="add_product_btn" class="btn green">Adicionar produto</a>
			<p id="total" class="btn grey" disabled>Total: R$ </p>
		</div>
		<div class="collection responsive-table">
			<table id="productInput" class="bordered highlight">
				<thead>
					<td><strong>Produto</strong></td>
					<td><strong>Valor unitário</strong></td>
					<td><strong>Quantidade</strong></td>
					<td><strong>Total</strong></td>
					<td><strong>Apagar</strong></td>
				</thead>
				<tbody id="newProduct">

				</tbody>
				<tbody id="enteredProducts">
					<?php foreach ($entry_data['entry'] as $prod) { ?>
					<tr>
						<td><?= $prod['name_product'] ?></td>
						<td><?='R$ ' . number_format($prod['unit_price_input'], 2, ',', '.');?></td>
						<td><?= $prod['amount_input'] ?></td>
						<td class="total" hidden><?= $prod['unit_price_input']*$prod['amount_input'] ?></td>
						<td><?='R$ ' . number_format($prod['unit_price_input']*$prod['amount_input'], 2, ',', '.');?></td>
						<td id="<?= $prod['id_product'] ?>">
									<input type="checkbox" class="filled-in" class="deleteProductBtn" id="ck<?= $prod['id_product'] ?>"/>
									<label for="ck<?= $prod['id_product'] ?>"> </label>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="add_product_modal" class="modal">
	<form id="generate_table_product">
		<div class="modal-content row bodyModal">
			<h4>Adicionar produto</h4>
			<div class="input-field col s12 m4">
				<select style="width: 100% !important" id="product_name" name="people"></select>
			</div>
			<div class="input-field col s12 m4">
				<input name="amount" required="required" type="number" placeholder="Quantia">
			</div>
			<div class="input-field col s12 m2">
				<input name="price" required="required" type="number" placeholder="Preço" step="0.00" min="0.00">
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn-flat modal-action modal-close">Cancelar</a>
			<button id="add_product_input_stock_btn" class="btn green">Adicionar<i class="material-icons right">send</i> </button>
		</div>
	</form>
</div>



<!--
		<div class="card-panel col s12 m12 l10">
			<div class="col s12 m6">
				<p><a id="add_product_btn" class="btn green">Adicionar produto</a></p>
			</div>
			<div class="right-align col s12 m6">
				<p id="total" class="btn grey" disabled>Total: R$ </p>
			</div>
		</div>
		<div class="container right-align col s12 m12 l10">
			<button id="add_product_input_stock_final_btn" type="submit" disabled="true" class="green btn-large">Finalizar<i class="material-icons right">send</i></button>
		</div>
		<div class="col s12 m12 l8">
			<div class="collection responsive-table">
				<table id="productInput" class="bordered highlight">
					<thead>
						<td><strong>Produto</strong></td>
						<td><strong>Valor unitário</strong></td>
						<td><strong>Quantidade</strong></td>
						<td><strong>Total</strong></td>
						<td><strong>Apagar</strong></td>
					</thead>
					<tbody id="newProduct">

					</tbody>
					<tbody id="enteredProducts">
						<?php foreach ($entry_data['entry'] as $prod) { ?>
						<tr>
							<td><?= $prod['name_product'] ?></td>
							<td><?='R$ ' . number_format($prod['unit_price_input'], 2, ',', '.');?></td>
							<td><?= $prod['amount_input'] ?></td>
							<td class="total" hidden><?= $prod['unit_price_input']*$prod['amount_input'] ?></td>
							<td><?='R$ ' . number_format($prod['unit_price_input']*$prod['amount_input'], 2, ',', '.');?></td>
							<td id="<?= $prod['id_product'] ?>">
							      <input type="checkbox" class="filled-in" class="deleteProductBtn" id="ck<?= $prod['id_product'] ?>"/>
							      <label for="ck<?= $prod['id_product'] ?>"> </label>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div id="add_product_modal" class="modal">
		<form id="generate_table_product">
			<div class="modal-content row bodyModal">
				<h4>Adicionar produto</h4>
				<div class="input-field col s12 m4">
					<input name="product_name" autocomplete="off" type="text" maxlength="45" placeholder="Produto">
					<div id="products" class="col s12 m12">
						<a href="#" id="loadProduct" class="col s12 m6"></a>
						<h5 id="product" class="col s12 m6"></h5>
					</div>
				</div>
				<div class="input-field col s12 m4">
					<input name="amount" required="required" type="number" placeholder="Quantia">
				</div>
				<div class="input-field col s12 m2">
					<input name="price" required="required" type="number" placeholder="Preço" step="0.00" min="0.00">
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn-flat modal-action modal-close">Cancelar</a>
				<button id="add_product_input_stock_btn" class="btn green">Adicionar<i class="material-icons right">send</i> </button>
			</div>
		</form>
	</div>
</div>-->
