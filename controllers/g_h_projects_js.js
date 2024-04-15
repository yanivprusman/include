// 2if(!window.g_h_projects_js_included){
function checkAndLoadjQuery(callback) {
  if (window.jQuery) {
    callback();
  } else {
    var script = document.createElement('script');
    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js'; // Update with the correct URL of the jQuery library
    script.onload = function() {
  callback();
  };
  document.head.appendChild(script);
  }
}
checkAndLoadjQuery(() => {});
  
function get(str){
    return $(`#${str}`);
}
function writeToDebugConsole(x,clear=false,pre=true){
    var element = debug.getElement();
    if (clear){
        element.html("");
        // $("#debugConsole").html("");
    }
    var written = false;
    var html="";
    try{
      if (typeof x === 'number') { // Check if x is a number
        html = String(x); // Convert x to a string
        written = true;
      } else {
        var html = js_beautify(x, { indent_size: 2 });
        written = true;
      }
    }catch(error){};
    if(!written){
      try{
        html=recurseArrayToString(x);
        written = true;
      }catch(error){};
    }
    if(!written){
      html="not written";
    }
    if (pre){
      element.html(element.html() + "<pre>" + html + "</pre>");
    }else{
      element.html(element.html() + html);
    }
}
class DebugOutput {
    constructor() {
      this.id="debugConsole";
      this.element = get(this.id);
    };
    toggle() {
      this.element = get(this.id);
      this.element.toggle();
    }
    getElement(){
      return this.element = get(this.id);;
    }
    write(x,clear=false){
        writeToDebugConsole(x,clear);
    }
    on(){
      //getElement();//.prop("style", "display: block;");
      this.getElement().prop("style", "display: block;");
    }
    clear(){
      this.getElement.html("");
    }
    debugTextToHtml(){
        this.element.html(this.getElement().text());
    }
};

const debug = new DebugOutput();
function recurseArrayToString(obj, indent = '') {
  let message = '';
  for (const prop in obj) {
    message += `${indent}${prop}: `;
    if (typeof obj[prop] === 'object') {
      message += '\n' + recurseArrayToString(obj[prop], indent + '  ');
    } else {
      message += `${obj[prop]}\n`;
    }
  }
  return message;
}

function makeUnsortedListExpandCollapse(container){
    if (container instanceof jQuery) {
        treeViewDiv = container[0];
    }else if (typeof container === 'string') {
        treeViewDiv = document.getElementById(containerId);
    }
    const treeNodes = treeViewDiv.querySelectorAll('li');
    treeNodes.forEach(node => {
        const childList = node.querySelector('ul');
        if (childList) {
            childList.style.display = 'none'; // Initially, hide all child nodes
            node.addEventListener('click', function() {
                event.stopPropagation();
                childList.style.display = (childList.style.display === 'none') ? 'block' : 'none';
            });
        }else {
            node.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevent the click event from propagating to the parent
            });
        }
    });
}
// function createNewResizableDiv({data,pre = false,after}) {
function createNewResizableDiv({data,pre = false,after}) {
    var newDiv = $('<div class="resizeableDivContainer" contenteditable="true" spellcheck="false">This is a new div</div>');
    get(after).after(newDiv);
    if (typeof data === 'object') {
        data = JSON.stringify(data);
    }
    if(pre){
        data = '<pre>' + data + '</pre>';
    }
    newDiv.html(data);
    makeUnsortedListExpandCollapse(newDiv);
}
function parsePHPVarDump(phpVarDumpText) {
    const parser = new DOMParser();
    const htmlDoc = parser.parseFromString(phpVarDumpText, 'text/html');
    const plainText = htmlDoc.body.textContent;
    const regex = /string '([^']*)'/g;
    const arrayValues = [];
    let match;
    while ((match = regex.exec(plainText)) !== null) {
        arrayValues.push(match[1]);
    }
    return arrayValues;
}
// function fetchPost({
//     className = '',
//     method =  '',
//     singleton = false,
//     singletonArguments,
//     methodArguments,
//     jsFetchArguments}
// ){
//     return fetch('',{
//         method :'POST',
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded'
//         },body: JSON.stringify({    
//             class:className,
//             method:method,
//             singleton:singleton,
//             singletonArguments:JSON.stringify(singletonArguments),
//             methodArguments:JSON.stringify(methodArguments),
//             jsFetchArguments:JSON.stringify(jsFetchArguments)
//         })
//     })
//     .then(response => response.json())
// }



