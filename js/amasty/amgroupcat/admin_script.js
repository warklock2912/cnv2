
Ext.extend(Ext.tree.CheckboxNodeUI, Ext.tree.TreeNodeUI, {
    /**
     * This is virtually identical to Ext.tree.TreeNodeUI.render, modifications are indicated inline
     */
    render : function(bulkRender){
        var n = this.node;
        var targetNode = n.parentNode ?
            n.parentNode.ui.getContainer() : n.ownerTree.container.dom; /* in later svn builds this changes to n.ownerTree.innerCt.dom */
        if(!this.rendered){
            this.rendered = true;
            var a = n.attributes;

            // add some indent caching, this helps performance when rendering a large tree
            this.indentMarkup = "";
            if(n.parentNode){
                this.indentMarkup = n.parentNode.ui.getChildIndent();
            }

            // modification: added checkbox
            var buf = ['<li class="x-tree-node"><div class="x-tree-node-el ', n.attributes.cls,'">',
                '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
                '<img src="', this.emptyIcon, '" class="x-tree-ec-icon">',
                '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on">',
                '<input class="l-tcb" '+ (n.disabled ? 'disabled="disabled" ' : '') +' type="checkbox" ', (a.checked ? "checked>" : '>'),
                '<a hidefocus="on" href="',a.href ? a.href : "#",'" ',
                a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '>',
                '<span unselectable="on">',n.text,"</span></a></div>",
                '<ul class="x-tree-node-ct" style="display:none;"></ul>',
                "</li>"];

            if(bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()){
                this.wrap = Ext.DomHelper.insertHtml("beforeBegin",
                    n.nextSibling.ui.getEl(), buf.join(""));
            }else{
                this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf.join(""));
            }
            this.elNode = this.wrap.childNodes[0];
            this.ctNode = this.wrap.childNodes[1];
            var cs = this.elNode.childNodes;
            this.indentNode = cs[0];
            this.ecNode = cs[1];
            this.iconNode = cs[2];
            this.checkbox = cs[3]; // modification: inserted checkbox
            this.anchor = cs[4];
            this.textNode = cs[4].firstChild;
            if(a.qtip){
                if(this.textNode.setAttributeNS){
                    this.textNode.setAttributeNS("ext", "qtip", a.qtip);
                    if(a.qtipTitle){
                        this.textNode.setAttributeNS("ext", "qtitle", a.qtipTitle);
                    }
                }else{
                    this.textNode.setAttribute("ext:qtip", a.qtip);
                    if(a.qtipTitle){
                        this.textNode.setAttribute("ext:qtitle", a.qtipTitle);
                    }
                }
            } else if(a.qtipCfg) {
                a.qtipCfg.target = Ext.id(this.textNode);
                Ext.QuickTips.register(a.qtipCfg);
            }

            this.initEvents();

            // modification: Add additional handlers here to avoid modifying Ext.tree.TreeNodeUI
            Ext.fly(this.checkbox).on('click', this.check.createDelegate(this, [null]));
            n.on('dblclick', function(e) {
                if( this.isLeaf() ) {
                    this.getUI().toggleCheck();
                }
            });

            if(!this.node.expanded){
                this.updateExpandIcon();
            }
        }else{
            if(bulkRender === true) {
                targetNode.appendChild(this.wrap);
            }
        }
    },

    checked : function() {
        return this.checkbox.checked;
    },

    /**
     * Sets a checkbox appropriately.  By default only walks down through child nodes
     * if called with no arguments (onchange event from the checkbox), otherwise
     * it's assumed the call is being made programatically and the correct arguments are provided.
     * @param {Boolean} state true to check the checkbox, false to clear it. (defaults to the opposite of the checkbox.checked)
     * @param {Boolean} descend true to walk through the nodes children and set their checkbox values. (defaults to false)
     */
    check : function(state, descend, bulk) {
        if (this.node.disabled) {
            return;
        }
        var n = this.node;
        var tree = n.getOwnerTree();
        var parentNode = n.parentNode;n
        if( !n.expanded && !n.childrenRendered ) {
            n.expand(false, false, this.check.createDelegate(this, arguments));
        }

        if( typeof bulk == 'undefined' ) {
            bulk = false;
        }
        if( typeof state == 'undefined' || state === null ) {
            state = this.checkbox.checked;
            descend = !state;
            if( state ) {
                n.expand(false, false);
            }
        } else {
            this.checkbox.checked = state;
        }
        n.attributes.checked = state;

        // do we have parents?
        if( parentNode !== null && state ) {
            // if we're checking the box, check it all the way up
            if( parentNode.getUI().check ) {
                // parentNode.getUI().check(state, false, true);
            }
        }
        if( descend && !n.isLeaf() ) {
            var cs = n.childNodes;
            for(var i = 0; i < cs.length; i++) {
                //cs[i].getUI().check(state, true, true);
            }
        }
         if( !descend && !n.isLeaf() ) {
             var cs = n.childNodes;
             for(var i = 0; i < cs.length; i++) {
                 cs[i].getUI().check(state, false, false);
             }
         }
        if( !bulk ) {
            tree.fireEvent('check', n, state);
        }
    },

    toggleCheck : function(state) {
        this.check(!this.checkbox.checked, true);
    }

});
