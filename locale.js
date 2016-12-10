(function(){
    var _constants = {};
    
    window.Locale = {
        load: function(constants) {
            _constants = constants;
        }
    };
    
    /**
     * Recebe uma string. Se achar tradução, retorna. 
     * Caso contrário, retorna a própria string.
     * 
     *      _('Administração') em pt-BR retorna 'Administração'
     *      mas em en-US vai achar a tradução e retornar 'Administration'
     * 
     */
    window.t = function(string) {
        return _constants[string]||string;
    }
}());