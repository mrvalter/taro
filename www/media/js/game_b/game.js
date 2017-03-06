class AutoSWManager
{
    constructor()
    {        
        this.tags = {};
		this.entities = {};
		this.events = {};
		this.DOMParse();
    }                  
    
	getEntityByName(name)
	{
		return this.entities[name];		
	}
	
	DOMParse()
	{
		var autoSwmanager = this;
		$('[data-sw]').each(function() {		
				
			let jsParts = $(this).data('sw').split(',');

			switch(jsParts[0].trim()){

				case 'value':
					if(jsParts[1] !== undefined){
						let entitiesParts = jsParts[1].split('.');					
						autoSwmanager.addHtmlTag(entitiesParts[0].trim(), entitiesParts[1].trim(), this, 'value');
					}
					break;

				case 'event':
					if(jsParts[1] === undefined || jsParts[2] === undefined){
						console.error('data-sw event must have 3 attributes');
					}

					let callbackName = jsParts[1];
					let entitiesParts = jsParts[2].split('.');
					autoSwmanager.addHtmlTag(entitiesParts[0].trim(), entitiesParts[1].trim(), this, 'event', callbackName.trim());
					break;
			}

			$(this).removeAttr('data-js');
		});
		
		return this;
	}
	
    addHtmlTag(entityName, propertyName, el, dataType, callbackName)
    {		
		
		if(this.tags[entityName] === undefined){
			this.tags[entityName] = {};
		}
		
        if(this.tags[entityName][propertyName] === undefined){
			this.tags[entityName][propertyName] = [];
		}				        

		var jqel = $(el);
        this.tags[entityName][propertyName].push({
           element : jqel,
           type: dataType,
		   callback: callbackName
        });
		
		return this;
    }
    
    replaceHtmlValuesByPropertyName(value, element)
    {    		
		element.html(value);        
    }
        
	addEntity(entity)
    {
        const entityName = entity.constructor.name;
				
		/* Смотрим все теги которые описаны в html */
		if(this.tags[entityName] === undefined){
			return this;
		}
		this.entities[entityName] = entity;							
		
    }
	
	addEvent(eventName, callback){
		this.events[eventName] = callback;
	}
	
	buildDependencies()
	{
		var gmanager = this;
		for (let entityName in this.entities){			
			var entity = this.entities[entityName];
			
			for(let oPropName in entity){						
				if(oPropName.indexOf("_") !== 0){
					continue;
				}

				let propertyName = oPropName.substr(1);

				if(this.tags[entityName] === undefined || this.tags[entityName][propertyName] === undefined || this.tags[entityName][propertyName].length === 0){
					Object.defineProperty(entity, propertyName, { 
						set: function (x) {                    
							this['_'+propertyName] = x;						
						},

						get: function () {                     
							return this['_'+propertyName];
						},
					});
					continue;
				}

				let callbacks = [];

				for (let tag of this.tags[entityName][propertyName]){					
					switch(tag.type){
						case 'value':
							
							callbacks.push(function(){gmanager.replaceHtmlValuesByPropertyName(entity['_'+propertyName], tag.element)});
							
							break;

						case 'event':	
							callbacks.push(function(val){
								return gmanager.events[tag.callback] !== undefined ? gmanager.events[tag.callback](tag.element, val, gmanager) : function(){};
							});
							
							break;

					}								

					tag.element.removeAttr('data-sw');

				}			
				
				Object.defineProperty(entity, propertyName, { 
					set: function (x) {                    
						this['_'+propertyName] = x;					
						for(let callback of callbacks){
							callback(x);
						}

					},

					get: function () {                     
						return this['_'+propertyName];
					},
				});								
			}
		}
	}
	
	draw(){
		
		this.buildDependencies();
		
		for (let entity in this.tags){
			
			for (let property in this.tags[entity]){
				
				for(let tag of this.tags[entity][property]){
					if(typeof tag.callback === 'function' && this.entities[entity] !== undefined && this.entities[entity][property] !== undefined){
						console.log(entity, property);
						tag.callback(this.entities[entity][property]);
					}
				}
			}
		}
	}
};

class Character
{
    constructor(id, name, sex, health, maxHealth, mana) {        
        this._id = id;
        this._name = name;
        this._sex = sex;
		this._health = health;
		this._maxHealth = maxHealth;
		this._mana = mana;
    }        
};