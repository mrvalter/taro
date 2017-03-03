class GManager
{
    constructor()
    {        
        this.objects = {};
    }       
       
    addEntity(entity)
    {
        const entityName = entity.constructor.name;
        
        if(this.objects[entityName] !== undefined){
            return entity;
        }
        
        this.objects[entityName] = {entityObject: entity, data: {}};        
        return entity;
    }
    
    addHtmlTag(entityName, propertyName, el, dataType)
    {
        if(this.objects[entityName] === undefined || this.objects[entityName].entityObject['_'+propertyName] === undefined){
            return false;
        }
        
        var entity = this.objects[entityName].entityObject;
        var gmanager = this;                
        
        if(this.objects[entityName].data[propertyName] === undefined){            
            this.objects[entityName].data[propertyName] = [];
                            
            Object.defineProperty(entity, propertyName, { 
                set: function (x) {                    
                    this['_'+propertyName] = x;
                    gmanager.replaceHtmlValuesByPropertyName(entityName, propertyName, x);
                },
                
                get: function () {                     
                    return this['_'+propertyName];
                },
            });            
        }        
        
        this.objects[entityName].data[propertyName].push({
           'element' : el,
           'type': dataType
        });
                
        this.replaceElementValue(el, dataType, entity[propertyName]);
                
    }
    
    replaceHtmlValuesByPropertyName(entityName, propertyName, value)
    {   
        
        if(this.objects[entityName] === undefined || this.objects[entityName].data[propertyName] === undefined){
            return false;
        }
                
        for(var elObj of this.objects[entityName].data[propertyName]){            
            this.replaceElementValue(elObj.el, elObj.type, value);
        }
        
    }
    
    replaceElementValue(el, type, value)
    {     
        console.log(el, type, value);
        switch(type){
            case 'html':
                $(el).html(value);
                break;
        }
    }
};

class Character
{
    constructor(id, name, sex) {        
        this._id = id;
        this._name = name;
        this._sex = sex;
    }        
};

var gManager  = new GManager();

$(window).on('load', function() {
    
    const character = new Character(12, 'john', 'male');        
    gManager.addEntity(character);      
    
    $('[data-html]').each(function() {                
        let arrEntities = $(this).data('html').split('.');
        gManager.addHtmlTag(arrEntities[0], arrEntities[1], this, 'html');
        $(this).removeAttr('data-html');
    });
    
    
    character.name = 'joHn';
    character.sex = 'male';
});
