function transaction(sqlTransactionArray,commit){
  return fetch('../include/transaction.php', {
      method: 'POST',
      headers: {
      'Content-Type': 'application/json'
      },
      body: JSON.stringify({action: 'transaction',commit:commit,sqlTransactionArray: sqlTransactionArray})
  })
  .then(response => {
      return response.clone().text()
      .then(text => {
          try {
              const data = JSON.parse(text);
              var responseArray = { 
                  ok: true,
                  commitable: data.commitable,
                  type: "json",
                  data: data };
              return responseArray;
          } catch (error) {
              var responseArray = { 
                  ok: false,
                  commitable: false,
                  type: "text",
                  data: text };
              return responseArray;
          }
      })
  })
}
function executeSql(sql,variables = null){
  var commit=true;
  var sqlTransactionArray = [];
  if(variables==null) variables = { };
  var sqlArray={sql:sql,variables:variables};
  sqlTransactionArray.push(sqlArray);
  return transaction(sqlTransactionArray,commit)
  .then(data=>{
    if(data.type=="text"){
      return data;
    }else{
      var response= data;
      response.data= data.data[0];
      return response;
    }
  });
}
async function populateSelect(selectElement,sql) {
    executeSql(sql)
    .then(data =>{
      if (data.commitable){
        rows=data.data["fetchAllResult"];
        selectElement.empty();
        if (Array.isArray(rows)){ 
            rows.forEach(row => {
                const optionElement = document.createElement('option');
                const keys = Object.values(row); // get an array of all the keys
                optionElement.value = keys[0];
                optionElement.text = keys[0];
                selectElement.append(optionElement);
            })
        }
      }else{
      }
    })        
    .catch(error => {
        console.error('in populateSelect Error:', error);
    });
}



