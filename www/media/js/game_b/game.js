class AutoSWManager
{
    constructor()
    {        
        this.tags = [];
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

            $(this).removeAttr('data-sw');
        });

        return this;
    }
	
	replaceHtmlValuesByPropertyName(value, element)
    {    		
        element.html(value);        
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
		
		this.tags.push({
		   entity: entityName,
		   property: propertyName,
           element : jqel,
           type: dataType,
		   callbackName: callbackName,
		   depending: 0
        });
		                
        return this;
    }        
        
    addEntity(entity)
    {
        const entityName = entity.constructor.name;				        
        this.entities[entityName] = entity;
		
		for (let ePropertyName in entity){
			if(ePropertyName.indexOf('_') !== 0){
				continue;
			}
			
			let propertyName = ePropertyName.substr(1);
			
			Object.defineProperty(entity, propertyName, { 
				set: function (value) {                    
						this['_'+propertyName] = value;
						this.runCallback(propertyName, value);
				},

				get: function () {                     
						return this['_'+propertyName];
				},
			});		
		}
		
		entity._callbacks = {};
		
		entity.runCallback = function(propertyName, value){
			if(this._callbacks[propertyName] === undefined){
				return true;
			}
			
			for(let callback of this._callbacks[propertyName]){
				if(typeof callback !== 'function'){
					continue;
				}
				callback(value);				
			}
		};
    }
	
    addEvent(eventName, callback){
        this.events[eventName] = callback;
    }
	
    buildDependencies()
    {
        var gmanager = this;
                  
		for(let tag of this.tags){
			let entityName = tag.entity;
			let propertyName = tag.property;
			
			if(this.entities[entityName] === undefined || this.entities[entityName]['_'+propertyName] === undefined){
				continue;
			}
			  
			var entity = this.entities[entityName];
			
			
			let callback = '';

			switch(tag.type){
				case 'value':
					callback = function(){gmanager.replaceHtmlValuesByPropertyName(entity['_'+propertyName], tag.element)};
					break;

				case 'event':	
					callback = function(val){
						return gmanager.events[tag.callbackName] !== undefined ? gmanager.events[tag.callbackName](tag.element, val, gmanager) : true;
					};

					break;
			}							
			
			tag.depending = 1;
			
			if(typeof callback !== 'function'){
				continue;
			}
			
			tag.callback = callback;
			
			if(entity._callbacks[propertyName] === undefined){
				entity._callbacks[propertyName] = [];
			}

			entity._callbacks[propertyName].push(callback);			
			tag.element.removeAttr('data-sw');                			
						
		}        		
    }
	
    draw(){
		
        this.buildDependencies();
		
		
        for (let tag of this.tags){
                        
			if(typeof tag.callback === 'function' && this.entities[tag.entity] !== undefined && this.entities[tag.entity][tag.property] !== undefined){
				console.log(tag.entity, tag.property);
				tag.callback(this.entities[tag.entity][tag.property]);
			}
                        
        }
		
		this.tags = [];
    }
};

class Character
{
    constructor(id, name, sex, health, maxHealth, mana, maxMana) {        
        this._id = id;
        this._name = name;
        this._sex = sex;
        this._health = health;
        this._maxHealth = maxHealth;
        this._mana = mana;
		this._maxMana = maxMana;
    }        
};