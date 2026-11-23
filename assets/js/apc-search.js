/**
 * AVALPAYCENTER - SEARCH & AUTOCOMPLETE ENGINE (UNIFICADO)
 * Búsqueda inteligente multi-palabra e insensible a tildes/acentos
 * para todas las categorías (Obligaciones Aval, Servicios Públicos, Conjuntos Residenciales).
 */
(function() {
    function normalizeText(str) {
        return (str || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function initApcSearch() {
        var searchInput = document.getElementById('search-input');
        if (!searchInput) return;

        var inputWrapper = searchInput.closest('.input-wrapper') || searchInput.parentElement;
        var inputContainer = searchInput.closest('.input-container') || searchInput.parentElement;
        var inputSearch = searchInput.closest('.input-search') || inputWrapper.parentElement;
        var label = inputContainer ? inputContainer.querySelector('.search-label') : document.querySelector('.search-label');

        // Crear o reutilizar dropdown
        var dropdown = document.getElementById('search-autocomplete-dropdown');
        if (!dropdown && inputSearch) {
            dropdown = document.createElement('div');
            dropdown.id = 'search-autocomplete-dropdown';
            dropdown.className = 'apc-search-dropdown';
            dropdown.innerHTML = '<ul id="search-autocomplete-list" class="apc-search-dropdown-list"></ul>' +
                '<div id="search-autocomplete-footer" class="apc-search-dropdown-footer" style="display:none;">' +
                    '<div class="footer-icon">!</div>' +
                    '<div>Existen más de 20 resultados, te sugerimos detallar tu búsqueda. Si quieres ver la lista completa da clic <a id="search-see-all">AQUÍ</a></div>' +
                '</div>';
            inputSearch.appendChild(dropdown);
        }

        var list = document.getElementById('search-autocomplete-list');
        var footer = document.getElementById('search-autocomplete-footer');
        var seeAllBtn = document.getElementById('search-see-all');

        // Catálogo para búsqueda
        var searchCatalog = [];
        if (typeof APC_GLOBAL_CATALOG !== 'undefined' && Array.isArray(APC_GLOBAL_CATALOG)) {
            searchCatalog = APC_GLOBAL_CATALOG;
        } else if (typeof allCards !== 'undefined' && Array.isArray(allCards)) {
            searchCatalog = allCards;
        }

        function updateLabelVisibility() {
            var hasText = searchInput.value.trim().length > 0;
            var isFocused = document.activeElement === searchInput;

            if (hasText || isFocused) {
                if (label) {
                    label.classList.add('hide-label', 'apc-hidden');
                    label.style.setProperty('display', 'none', 'important');
                    label.style.setProperty('opacity', '0', 'important');
                    label.style.setProperty('visibility', 'hidden', 'important');
                }
                searchInput.classList.add('apc-has-text', 'has-content');
                if (inputWrapper) inputWrapper.classList.add('is-focused');
            } else {
                if (label) {
                    label.classList.remove('hide-label', 'apc-hidden');
                    label.style.removeProperty('display');
                    label.style.removeProperty('opacity');
                    label.style.removeProperty('visibility');
                }
                searchInput.classList.remove('apc-has-text', 'has-content');
                if (inputWrapper) inputWrapper.classList.remove('is-focused');
            }
        }

        function highlightMatch(text, query) {
            if (!query) return text;
            var cleanQ = normalizeText(query);
            var tokens = cleanQ.split(/\s+/).filter(Boolean);
            if (!tokens.length) return text;

            var accMap = {
                'a': '[aáAÁ]',
                'e': '[eéEÉ]',
                'i': '[iíIÍ]',
                'o': '[oóOÓ]',
                'u': '[uúüUÚÜ]',
                'n': '[nñNÑ]'
            };

            var patternParts = tokens.map(function(t) {
                var p = '';
                for (var i = 0; i < t.length; i++) {
                    var ch = t[i].toLowerCase();
                    p += accMap[ch] || t[i].replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                }
                return p;
            });

            try {
                var regex = new RegExp('(' + patternParts.join('|') + ')', 'gi');
                return text.replace(regex, '<span class="highlight-match">$1</span>');
            } catch (e) {
                return text;
            }
        }

        function doSearch(query) {
            updateLabelVisibility();
            var rawQ = (query || '').trim();
            var cleanQ = normalizeText(rawQ);

            if (!cleanQ) {
                if (dropdown) dropdown.style.display = 'none';
                if (typeof renderPage === 'function' && typeof filteredCards !== 'undefined') {
                    filteredCards = null;
                    renderPage(1);
                }
                return;
            }

            var tokens = cleanQ.split(/\s+/).filter(Boolean);

            var results = searchCatalog.filter(function(item) {
                var name = normalizeText(item.name);
                var id = normalizeText(item.idConv);
                var cat = normalizeText(item.category);
                return tokens.every(function(t) {
                    return name.indexOf(t) !== -1 || id.indexOf(t) !== -1 || cat.indexOf(t) !== -1;
                });
            });

            // Si estamos en categoria-obligaciones.html, sincronizar filteredCards local
            if (typeof renderPage === 'function' && typeof allCards !== 'undefined') {
                filteredCards = allCards.filter(function(c) {
                    var n = normalizeText(c.name);
                    var id = normalizeText(c.idConv);
                    return tokens.every(function(t) {
                        return n.indexOf(t) !== -1 || id.indexOf(t) !== -1;
                    });
                });
                renderPage(1);
            }

            if (!dropdown || !list) return;

            if (results.length === 0) {
                list.innerHTML = '<li class="apc-search-dropdown-item" style="color:#627d98;font-style:italic;cursor:default;">No se encontraron convenios</li>';
                if (footer) footer.style.display = 'none';
                dropdown.style.display = 'block';
                return;
            }

            var limit = 7;
            var displayItems = results.slice(0, limit);

            list.innerHTML = displayItems.map(function(item) {
                var safeName = (item.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var highlighted = highlightMatch(safeName, rawQ);
                var displayId = item.idConv || '';
                var cat = item.category || 'Servicios Públicos';
                var catClass = 'cat-publicos';
                if (cat === 'Obligaciones Aval') catClass = 'cat-obligaciones';
                else if (cat === 'Conjuntos Residenciales') catClass = 'cat-conjuntos';

                return '<li class="apc-search-dropdown-item" data-id="' + displayId + '" data-name="' + encodeURIComponent(item.name || '') + '" data-category="' + encodeURIComponent(cat) + '" data-target="' + (item.targetPage || '') + '">' +
                    '<div class="item-content">' +
                        '<div class="item-info">' +
                            '<span class="item-title">' + highlighted + '</span>' +
                            '<span style="font-size:11px;color:#829ab1;font-weight:500;">Código / Ref: ' + displayId + '</span>' +
                        '</div>' +
                        '<span class="item-category-tag ' + catClass + '">' + cat + '</span>' +
                    '</div>' +
                '</li>';
            }).join('');

            if (footer) {
                footer.style.display = results.length > 5 ? 'flex' : 'none';
            }
            dropdown.style.display = 'block';

            // Click en ítem
            list.querySelectorAll('.apc-search-dropdown-item').forEach(function(el) {
                el.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var cid = el.getAttribute('data-id') || '';
                    var cname = decodeURIComponent(el.getAttribute('data-name') || '');
                    var ccat = decodeURIComponent(el.getAttribute('data-category') || '');
                    var ctarget = el.getAttribute('data-target') || '';
                    
                    dropdown.style.display = 'none';
                    searchInput.value = cname;
                    updateLabelVisibility();

                    var target = ctarget || 'pago-form.html';
                    var cidUpper = cid.toUpperCase();
                    var cnameUpper = cname.toUpperCase();

                    // Toda opción de Obligaciones Aval o entidades bancarias va al formulario con valor a pagar libre
                    if (ccat === 'Obligaciones Aval' || cid.startsWith('O0') || cid === '00026635' || cid === 'occidente-ordinaria' || cnameUpper.includes('BANCO') || cnameUpper.includes('CRÉDITO') || cnameUpper.includes('CREDITO') || cnameUpper.includes('LIBRANZA')) {
                        target = 'pago-obligaciones-financieras.html';
                    } else if (ccat === 'Conjuntos Residenciales' || cnameUpper.includes('CONJUNTO') || cnameUpper.includes('PH') || cnameUpper.includes('EDIFICIO') || cnameUpper.includes('RESIDENCIAL')) {
                        target = 'pago-conjuntos.html';
                    } else if (target === 'pago-form.html') {
                        target = 'pago-facturadores.html';
                    }

                    var url = target + '?idConv=' + encodeURIComponent(cid) + '&origen=buscar&name=' + encodeURIComponent(cname);
                    if (typeof showPageLoader === 'function') {
                        showPageLoader(url, 400);
                    } else {
                        window.location.href = url;
                    }
                };
            });
        }

        if (seeAllBtn) {
            seeAllBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (dropdown) dropdown.style.display = 'none';
                window.location.href = 'que-puedo-pagar.html';
            };
        }

        // Eventos
        searchInput.addEventListener('focus', function() {
            updateLabelVisibility();
            if (searchInput.value.trim().length > 0) {
                doSearch(searchInput.value);
            }
        });

        searchInput.addEventListener('blur', function() {
            setTimeout(function() {
                updateLabelVisibility();
                if (dropdown) dropdown.style.display = 'none';
            }, 250);
        });

        searchInput.addEventListener('input', function() {
            updateLabelVisibility();
            doSearch(searchInput.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var rawVal = searchInput.value.trim();
                var normQ = normalizeText(rawVal);
                if (dropdown) dropdown.style.display = 'none';

                if (!normQ) return;

                // Si estamos en categoria-obligaciones.html, usar la función local si existe
                if (typeof handleSearch === 'function') {
                    handleSearch(rawVal);
                    return;
                }

                var tokens = normQ.split(/\s+/).filter(Boolean);
                var matches = searchCatalog.filter(function(item) {
                    var n = normalizeText(item.name);
                    var id = normalizeText(item.idConv);
                    var cat = normalizeText(item.category);
                    return tokens.every(function(t) {
                        return n.indexOf(t) !== -1 || id.indexOf(t) !== -1 || cat.indexOf(t) !== -1;
                    });
                });

                var isObligaciones = normQ.includes('obligac') || normQ.includes('banco') || normQ.includes('credito') || normQ.includes('hipotec') || normQ.includes('tarjet') || normQ.includes('libranz') || normQ.includes('financier') || normQ.includes('prestam') || normQ.includes('vehicul') || (matches.length > 0 && matches[0].category === 'Obligaciones Aval');
                
                var isConjuntos = normQ.includes('conjunt') || normQ.includes('residenc') || normQ.includes('inmobiliari') || normQ.includes('ph') || normQ.includes('edifici') || (matches.length > 0 && matches[0].category === 'Conjuntos Residenciales');

                if (isObligaciones) {
                    window.location.href = 'categoria-obligaciones.html?q=' + encodeURIComponent(rawVal);
                } else if (isConjuntos) {
                    window.location.href = 'categoria-conjuntos.html?q=' + encodeURIComponent(rawVal);
                } else {
                    window.location.href = 'categoria-publicos.html?q=' + encodeURIComponent(rawVal);
                }
            }
        });

        // Click fuera para cerrar dropdown
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && dropdown && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Botón Buscar (Lupa o botón primario contiguo)
        var searchBtn = document.querySelector('.button-desktop .btn-primary, button.btn-primary');
        if (searchBtn && !searchBtn.closest('apc-pop-up')) {
            searchBtn.addEventListener('click', function(e) {
                var q = searchInput.value.trim();
                if (q) {
                    doSearch(q);
                }
            });
        }

        // Chequeo inicial
        updateLabelVisibility();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApcSearch);
    } else {
        initApcSearch();
    }
})();
