var formstationsclose = true;

var Mywindow1 = null;
var formCreate = null;
function CreateDossier()
{
	if(!formstationsclose)
		return;
	
	
	Ext.onReady(function(){

		var MesBouttons = [{
	        text: 'Annuler',
			handler : function() {
				
				Mywindow1.destroy();
				
			}
	    }];
	
		
		 formCreate = new Ext.form.FormPanel({
		        baseCls: 'x-plain',
		        layout:'absolute',
		        frame  :true,
		        autoLoad :"../StationBack/formulaire_short.php",
		        
		        defaultType: 'textfield',
				 buttons: MesBouttons
		    });		
		    
		    
			Mywindow1 = new Ext.Window({
		       title: 'Nouveau dossier',
		       width: 275,
		       height:400,
		       y:280,
		       minWidth: 275,
		       minHeight: 400,
		       layout: 'fit',
		       plain:true,
		       modal:true,
		       buttonAlign:'center',
		       autoLoad :"../StationBack/formulaire_short.php",
		       buttons: MesBouttons
			
		   	});
			
			formstationsclose = false;
			
			Mywindow1.show();
			
			Mywindow1.on("destroy",function(){
				
				formstationsclose = true;
					
				
				
				});
		
	});	
}