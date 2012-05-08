//jquery range plugin
(function($){
	
$.range = function(selection,_document){
	

	var
		// 共通して使うメソッド
		common = {
			 _from :  0, // 先頭からの選択開始位置
			 _to :  0,	 // 先頭からの選択終了位置
		
			 /**
				* 制御文字を除いた文字列の長さを返す
				* <s>String.replaceで改行コードが削除できないので、一文字ずつ見て文字列の長さを数える</s>
				* 上の文は、間違い。RegExpの第二引数に"g"オプション渡してなかったorz 修正中
				* @param str 長さを数えたい文字列
				* @return 文字列の長さ
				*/
			 count :  function(str) {
				 var
				len = 0,
				unnecessity = '[' + String.fromCharCode(0) + '-' + String.fromCharCode(31) + ']', // ASCIIコードの0-31は制御文字なので排除
				pattern = new RegExp(unnecessity);
		
				 for (var i = 0, l = str.length; i < l; i++) {
				if (!pattern.test(str.charAt(i)))
					len++;
				 }
		
				 return len;
			 },
				
			/**
			 * 親要素を取得
			 */
			 parent :  function(){
			 	var parent = (this.commonAncestorContainer) ? this.commonAncestorContainer : this.parentElement();
			 	return parent;
			 },
			 
		
			 /**
				* 先頭から選択開始位置までの情報を(取得)
				*/
			 from :  function() {
				 switch (arguments.length) {
				case 0:
					return this._from;
				case 1:
					break;
				 }
		
				 return this;
			 },
		
			 /**
				* 先頭から選択終了位置までの情報を(取得)
				*/
			 to :  function() {
				 switch (arguments.length) {
				case 0:
					return this._to;
				case 1:
					break;
				 }
		
				 return this;
			 },
		
			/**
			 * 先頭からの選択位置情報を取得
			 * @return [選択開始位置, 選択終了位置]
			 */
			pos :  function() {
				return [this._from, this._to];
			},
		
			 /**
				* 選択開始ノード、位置を(取得|設定)
				*/
			start :  function() {
				 switch (arguments.length) {
				case 0:
					return [this.startContainer, this.startOffset];
				case 1:
				case 2:
					this._setStart.apply(this, arguments);
				 }
		
				 return this;
			 },
		
			/**
			 * 選択終了ノード、位置を(取得|設定)
			 */
			end :  function() {
				 switch (arguments.length) {
				case 0:
					return [this.endContainer, this.endOffset];
				case 1:
				case 2:
					this._setEnd.apply(this, arguments);
				 }
		
				 return this;
			 },
			 
			 move : function(node) {
			 	return this.caret(node);
			 }
		
		};

		// DOM Level 2 Rangeをサポートしているかどうかでラッパーを変える
		var exclusive = (function() {
			var m;

			 // DOM Level 2 Rangeをサポートしているブラウザ向けメソッド
			 if (_document.createRange && Range.prototype.createContextualFragment != undefined) {
				 m = {
					_setStart :  function(node, offset) {
						// 作業用Range作成
						var range = _document.createRange();
			
						// テキストノードじゃないとsetStartできないのでテキストノード出るまで子ノード見てく
						// 改善する必要あり
						while (node.nodeType == 1) {
							node = node.firstChild;
						}
			
						this.setStart(node, offset || 0);
			
						// body要素を選択し、選択終了位置を_setStartの引数で再設定する
						// 文字数を数えると先頭からの位置が割り出せるという寸法
						range.selectNode(_document.body);
						range.setEnd(node, offset || 0);
						this._from = this.count(range.toString());
					},
			
					_setEnd :  function(node, offset) {
						var range = _document.createRange();
			
						while (node.nodeType == 1) {
							node = node.firstChild;
						}
			
						this.setEnd(node, offset || 0);
			
						range.selectNode(_document.body);
						range.setEnd(node, offset || 0);
						this._to = this.count(range.toString());
					},
			
					/**
					 * select node
					 * @param node
					 * @return range
					 */
					 sel :  function(node) {
						 node = $(node)[0];
						 this.selectNode(node);
						 return this;
					 },
			
					/**
					 * 選択範囲内のテキストを引数で指定した要素で囲む
					 * @param elem jquery object
					 * @return range
					 */
					 wrap :  function(elem) {
						elem = $(elem,_document)[0];
						if (this.startContainer == this.endContainer) {
						 	this.surroundContents(elem);
						}
						return this; 
					 },
				 
					 /**
						* paste html
						* @param {string} html
						*/
					 paste : function(html){
						node = this.createContextualFragment(html);
						this.deleteContents();
						this.insertNode(node);
					 },
					 
					 /**
					  *		set caret
					  */
					 caret : function(node){
					 	if(!node)return;
					 	this.sel(node);
					 	this.deleteContents();
					 	selection.removeAllRanges();
					 	selection.addRange(this);
					 },
					 
					 /**
					  * 
					  */
					elements : function(){
						//not separeted
						if (this.startContainer == this.endContainer) {
						 	elem = $("<span id='__range__'></span>",_document)[0];
							this.surroundContents(elem);
							return [$("#__range__",_document).removeAttr("id","")[0]];
						}else{
							_elements = [];
							
							if(this.startContainer.nodeType == Node.TEXT_NODE){
								var newone = this.startContainer.splitText(this.startOffset);
								var newspan = _document.createElement('span');
								var range2 = _document.createRange();
								range2.selectNode(newone);
								range2.surroundContents(newspan);
								range2.detach();	//役目終了
								this.setStartBefore(newspan);	//開始点再設定
							}
							if(this.endContainer.nodeType == Node.TEXT_NODE){
								this.endContainer.splitText(this.endOffset);
								var newspan = _document.createElement('span')
								var range2 = _document.createRange();
								range2.selectNode(this.endContainer);
								range2.surroundContents(newspan);
								range2.detach();	//役目終了
								this.setEndAfter(newspan);	//開始点再設定
							}
				
							var currentNode = this.startContainer;
							var tmpRange = _document.createRange();	//Rangeを作っておく
							while(true){
								tmpRange.selectNode(currentNode);
								
								//作業用rangeがはみ出ている場合
								if(tmpRange.compareBoundaryPoints(Range.START_TO_START,this)==-1 ||
								   tmpRange.compareBoundaryPoints(Range.END_TO_END,this)==1){
									if(tmpRange.compareBoundaryPoints(Range.START_TO_END,this)<=0){
										while(currentNode.parentNode || currentNode.nextSibling){
											if(currentNode.nextSibling){
												currentNode=currentNode.nextSibling;
												break;
											}
											currentNode=currentNode.parentNode;
										}
										continue;
									}else if(tmpRange.compareBoundaryPoints(Range.END_TO_START,this)>=0){	//後ろにある場合
										break;
									}else{
										if(currentNode.firstChild){
											currentNode = currentNode.firstChild;
											continue;
										}else{
											break;
										}
									}
								}else{
									//はみ出ていない場合
									//親がはみ出ていないかチェック
									tmpRange.selectNode(currentNode.parentNode);
									if(tmpRange.compareBoundaryPoints(Range.START_TO_START,this)>=0 &&
									   tmpRange.compareBoundaryPoints(Range.END_TO_END,this)<=0){
										//はみ出ていなければ
										currentNode = currentNode.parentNode;
										continue;
									
									//開始
									}else{
										//親がはみ出ていなければスタイル指定
										if(currentNode.nodeType == Node.TEXT_NODE){
											//テキストノードだったら、span要素で囲む
											var newspan = _document.createElement('span');
											tmpRange.selectNode(currentNode);
											tmpRange.surroundContents(newspan);
											_elements.push(newspan);
											
										}else{
											_elements.push(currentNode);
										}
										//親兄弟が
										while(currentNode.parentNode || currentNode.nextSibling){
											if(currentNode.nextSibling){
												currentNode=currentNode.nextSibling;
												break;
											}
											currentNode=currentNode.parentNode;
										}
										continue;
									}
								}
							}
							tmpRange.detach(); //release
						}
						
						return _elements;
					},
					
					contents : function(node){
						this.selectNodeContents(node);
					},
					
					select : function(){
						 //do nothing
					 }
					
			 }	//m
				 
			 
		 // IE用
		 } else if (_document.body.createTextRange) {
			 m = {
				collapsed :  false, // Rangeのstartとendの位置が同じかどうか真偽値を返す
				startContainer :  null,
				startOffset :  0,
				endContainer :  null,
				endOffset :  0,
				
				_initRange :  function(selection,_document){
					this.collapsed = (!this.text || this.text.length < 1);
					
					if(this.item){
						try {
							this.startContainer = this.item(0);
							this.endContainer = this.item(this.item.length - 1);
						}catch(e){
								
						}
					}else{
						this.startContainer = this.endContainer = this.parentElement();
					}
					
					try{
						var docRange = _document.selection.createRange();
						var textRange = _document.body.createTextRange();
						var elm = textRange.parentElement();
						textRange.moveToElementText(elm);
						var range = textRange.duplicate();
						range.setEndPoint('EndToStart', docRange);
						this.startOffset = range.text.length;
						
						var range = textRange.duplicate();
						range.setEndPoint('EndToEnd', docRange);
						this.endOffset = range.text.length;
					} catch(e) {
						
					}
				},
		
				setCollapsed :  function() {
					if ( (this.startContainer && this.endContainer)
					 &&	 (this.startContainer == this.endContainer)
					 && (this.startOffset == this.endOffset) ){
						this.collapsed = true;
					}else{
						this.collapsed = false;
					}
				},
		
				_setStart :  function(node, offset) {
					// 作業用Range作成
					var range = _document.body.createTextRange();
		
					// 選択終了位置が未設定の場合は設定する
					if (this.endContainer == null) {
						this.moveToElementText(node);
						this.endContainer = node;
						this.endOffset = node.innerText.length;
					}
		
					// 選択開始ノードと位置を保持
					this.startContainer = node;
					this.startOffset = offset || 0;
		
					// 選択開始ノードと位置を実際に設定
					range.moveToElementText(node);
					range.moveStart('character', this.startOffset);
					this.setEndPoint('StartToStart', range);
		
					// ページ先頭からの位置を取得
					range.setEndPoint('EndToStart', range);
					range.setEndPoint('StartToStart', _document.body.createTextRange());
					this._from = this.count(range.text);
		
					this.setCollapsed();
				},
		
				_setEnd :  function(node, offset) {
					// 作業用Range作成
					var range = _document.body.createTextRange();
		
					// 選択開始位置が未設定の場合は設定する
					if (this.startContainer == null) {
						this.moveToElementText(node);
						this.startContainer = node;
						this.startOffset = 0;
					}
		
					// 選択終了ノードと位置を保持
					this.endContainer = node;
					this.endOffset = offset || 0;
		
					// 選択終了ノードと位置を実際に設定
					range.moveToElementText(node);
					range.moveStart('character', this.endOffset);
					this.setEndPoint('EndToStart', range);
		
					// ページ先頭からの位置を取得
					range.setEndPoint('EndToStart', range);
					range.setEndPoint('StartToStart', _document.body.createTextRange());
					this._to = this.count(range.text);
		
					this.setCollapsed();
				},
		
				sel :  function(node) {
					if(!node)return this;
					if(!node.tagName)return this;
					if(node.tagName == "BODY")return this;
					
					this.startContainer = node;
					this.startOffset = 0;
					this.endContainer = node;
					this.endOffset = node.innerText.length;
					this.setCollapsed();
					
					this.moveToElementText(node);
					
					return this;
				},
		
				wrap :  function(elem) {
					// ここもキモ
					elem.innerHTML = this.text;
					this.pasteHTML(elem.outerHTML);
					this.sel(elem);
					return this; 
				},
					
				paste : function(html){
					this.pasteHTML(html);
					if(this.item){
						this.sel(this.item(0));
					}
				},
		
				detach :  function() {
					this.setEndPoint('EndToStart', this);
				},
				
				createContextualFragment : function(html){
					var div = $("div").html(html)[0];
					
					if(div.childNodes.length < 2)return div.childNodes[0];
					return div;
				},
				
				/**
				  *	 set caret
				  */
				 caret : function(node){
				 	if(!node)return;
				 	this.sel(node);
					$(node).remove();
				 },
				 
				 move : function(node){
					this.moveToElementText(node);
					this.select(node);
					this.caret(node);
				 },
				 
				 /**
				  * 選択範囲内の全ての要素を返す
				  */
				 elements : function(){
					if(this.parentElement){
						ele = this.parentElement();
						if(ele.nodeType == 3){
							ele = ele.parentNode;
						}
						return [ele];
					}
					return this.item;
				 }
			};
			 
		 } else {
			 alert('do not support range');
			 throw 'Can not use exRange.js';
		 }

		 return m;
	})();
	
	

	// Rangeオブジェクト取得
	var range = (function() {
 
		var r = new Object();
		
		if (_document.createRange && Range.prototype.createContextualFragment != undefined) {
			r = (selection && selection.rangeCount) ? selection.getRangeAt(0) : _document.createRange();
			
		} else if (_document.body.createTextRange){
			r = (selection) ? selection.createRange() : _document.body.createTextRange() ;
		}
		
		return r;
	})();

	// 共通メソッドを設定
	for (var o in common)
 		range[o] = common[o];

	// ブラウザ別ラッパーメソッドを設定
	for (var o in exclusive){
		range[o] = exclusive[o];
	}
	
	if(range._initRange){
		range._initRange(selection,_document);
	}
	
	return range;

};

})(jQuery);
