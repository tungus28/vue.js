<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//die();
$APPLICATION->SetTitle("Количество сотрудников в Юр. лицах");
$isAdmin = false;

if(in_array(CUser::GetID(), [ 4422, 14805, 486 ] ) ) {
    $isAdmin = true;
}

if( $isAdmin || in_array(CUser::GetID(),  getAllDepartWorkersRecursive([10563])) ) {
//
} else {
    die('Нет доступа!');
}

function getAllDepartWorkersRecursive($ar_departs) {
	if(CModule::IncludeModule("intranet")){
		$arManagersWithInfo = CIntranetUtils::GetDepartmentManager($ar_departs, $skipUserId=false, $bRecursive=true);

			$ar_managers_ids = [];
				foreach($arManagersWithInfo as $k=>$v) {
					$ar_managers_ids[] = $k;
			}
			
			$ar_workers_ids = [];
			
	   $arUsers = CIntranetUtils::GetDepartmentEmployees($ar_departs, $bRecursive = true, $bSkipSelf = false, $onlyActive = 'Y');
	   //$arUsers = CIntranetUtils::GetSubordinateEmployees($HeadID, true);
	   while($User = $arUsers->GetNext()){
		  $ar_workers_ids[] = $User[ID];
	   }
	
	return array_merge($ar_managers_ids, $ar_workers_ids);
	
	}
	
	return [];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags --> 
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    
   

    <!-- Required Stylesheets -->
    <link
      type="text/css"
      rel="stylesheet"
      href="https://unpkg.com/bootstrap/dist/css/bootstrap.min.css"
    />
    <link
      type="text/css"
      rel="stylesheet"
      href="https://unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.css"
    />
     
      <!-- MD icons -->
    
     <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.1/css/all.css" integrity="sha384-IT8OQ5/IfeLGe8ZMxjj3QQNqT0zhBJSiFCL3uolrGgKRuenIU+mMS94kck/AHZWu" crossorigin="anonymous">
     
    <!-- Load polyfills to support older browsers -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es2015%2CIntersectionObserver"></script>

    <!-- Required scripts -->
    <script src="https://unpkg.com/vue@latest/dist/vue.js"></script>
    <script src="https://unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.js"></script>
  </head>
  <body>
    <!-- Our application root element -->
    <div id="app"> 
        <p> 
        
        <font-awesome-icon name="user"></font-awesome-icon>
        
        <font-awesome-icon icon="coffee"></font-awesome-icon>
       <b-col lg="6" class="my-1">
        <b-form-group
          label="Фильтр"
          label-cols-sm="3"
          label-align-sm="left"
          label-size="sm"
          label-for="filterInput"
          class="mb-0"
        >
          <b-input-group size="sm">
            <b-form-input
              v-model="filter"
              type="search"
              id="filterInput"
              placeholder="Поиск"
            ></b-form-input>
            <b-input-group-append>
              <b-button :disabled="!filter" @click="filter = ''">Очистить</b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
      </b-col>
      </p>
    
      <b-table      
      :filter="filter"
      :filterIncludedFields="filterOn"
      @filtered="onFiltered"
      :items="items"
      :fields="fields"
      :sort-by.sync="sortBy"
      :sort-desc.sync="sortDesc"
      sort-icon-left
      responsive="sm"
      :sticky-header="sticky_header"
      :head-variant="headVariant"
      
      striped 
      bordered
      
      borderless 
      outlined
      small
      
       hover
      :table-variant="tableVariant"
      fixed
      
      >
          <template v-slot:cell(max_workers_cnt)="data">
            <!-- `data.value` is the value after formatted by the Formatter -->
            <!--a :href="`#${data.value.replace(/[^a-z]+/i,'-').toLowerCase()}`">{{ data.value }}</a-->
             <b-container>
                <b-row class="justify-content-md-center">
                    <b-form-input <?if(!$isAdmin) echo 'disabled';?> v-model="data.value" style="width: 60%; height: 36px;" size="sm" small :id="`index-${data.index}`" type="number" value="`${data.value}`"></b-form-input>
                    <i <?if(!$isAdmin) echo 'hidden';?> v-on:click.stop="saveMaxWorkers(data.item, data.index, data.value)" name="max_urlico_workers" style="color: green; cursor: pointer; padding: 0 0 0 15px; font-size: 36px;" class="far fa-save"></i>
                </b-row>
             </b-container>
          </template>
      
      </b-table>
      
      <!--div>
      Sorting By: <b>{{ sortBy }}</b>, Sort Direction:
      <b>{{ sortDesc ? 'Descending' : 'Ascending' }}</b>
    </div-->
      
    </div>
<?
global $DB;

$str = str_replace(" ", "", $str);
		$str = str_replace("'", "", $str);
		$str = str_replace('"', "", $str);
		$str = str_replace('«', "", $str);
		$str = str_replace('»', "", $str);		
		$str = str_replace('&quot;', "", $str);	
 
 $sql = 'select t2.uf_ur_lico as urlico,
            (count(t2.value_id) - count(t3.group_id)) as roznica_workers_cnt,
            count(t3.group_id) as office_workers_cnt,
            count(t2.value_id) as all_workers_cnt,
            if(t4.max_workers > 0, t4.max_workers, 99) as max_workers_cnt

            from b_uts_user t2

            join b_user t1
            on t2.value_id = t1.id and t1.active = "Y"

            left join b_user_group t3
            on t2.value_id = t3.user_id and t3.group_id = 11
            
            left join urlico_max_workers as t4
            on t4.urlico = replace(replace(replace(replace( replace(t2.uf_ur_lico, " ", ""), "\"", "" ), "&quot;", ""), "«", ""), "»", "")


            where length(t1.work_position) > 0
            and length(t2.uf_ur_lico) > 0
            group by t2.uf_ur_lico
            order by uf_ur_lico asc';
  
  
  $rs = $DB->Query($sql, false, $err_mess.__LINE__);	
	
  while ( $ar = $rs->GetNext() ) {
    $obj[] = [ 'urlico' => $ar['urlico'],
               'roznica_workers_cnt' => $ar['roznica_workers_cnt'],
               'office_workers_cnt' => $ar['office_workers_cnt'],
               'all_workers_cnt'    => $ar['all_workers_cnt'],
               'max_workers_cnt'    => $ar['max_workers_cnt'],
    
             ];
  }
  

$urlico = json_encode($obj);


?>
    <!-- Start running your app -->
    <script>
     var urlico = JSON.parse('<?php echo addslashes(json_encode($obj,JSON_HEX_APOS | JSON_HEX_QUOT)) ?>');
      
      window.app = new Vue({
        el: '#app',               
        name: 'urlicoTable',
        props:['proplabels'],
        data: {
           sortBy: 'urlico',
           sortDesc: false,           
           filter: null,
           filterOn: [],
           tableVariant: 'warning',
           headVariant: 'light',
           sticky_header: "800px",
           items: urlico,
         fields: [
          { key: 'urlico', 
            formatter: (value, key, item) => {
                return value.replace(/&quot;/g,'"');
            },          
            label: 'Юридическое лицо', sortable: true, sortDirection: 'asc' },
          { key: 'roznica_workers_cnt', label: 'Кол-во сотрудников розницы', sortable: true, class: 'text-center' },
          { key: 'office_workers_cnt', label: 'Кол-во сотрудников офиса', sortable: true, class: 'text-center' },
          { key: 'all_workers_cnt', label: 'Общее кол-во сотрудников', sortable: true, class: 'text-center' },
          { key: 'max_workers_cnt',
            //editable: true,
            formatter: (value, key, item) => {
               return value;
               
               /*let min = 1;
               let max = 19;
               let rand = min - 0.5 + Math.random() * (max - min + 1);             
               
               return value*1 + Math.round(rand);*/
                               
            },          
            label: 'Максимально возможное кол-во сотрудников', 
            sortable: true, class: 'text-center' },
          /*{
            key: 'isActive',
            label: 'is Active',
            formatter: (value, key, item) => {
              return value ? 'Yes' : 'No'
            },
            sortable: true,
            sortByFormatted: true,
            filterByFormatted: true
          },
          { key: 'actions', label: 'Actions' }*/
        ],
        },
        methods: {
            saveMaxWorkers: function (item, index, max_workers_value) {
             
              var newMaxObj = {};
              
              var my_items = this.items;
              
              var newIndex;
              
              my_items.map(function(newMaxObj, indx, my_items) {               
                if(item.urlico == my_items[indx].urlico) {
                    newIndex = indx;
                    console.log(newIndex);
                }
              });
              
              //меняем данные в объекте vue
              this.items[newIndex].max_workers_cnt =  max_workers_value;
              
              
              //console.log(max_workers_value);              
              
              axios.post('ajax.php', {
                    'type': 'change_max_workers',
                    'urlico': item.urlico,
                    'max_workers_cnt': max_workers_value
                  })
                  .then(response => {
                    console.log(response.data);
                    alert('Данные сохранены')
                  })
                  .catch(error => {
                    console.log(error);
                    this.errored = true;
                  })
                  .finally(() => (this.loading = false));
              
              
              // `event` — нативное событие DOM event.target.tagName
             
            },            
          }

      })
            
    </script>
  </body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); 
?>