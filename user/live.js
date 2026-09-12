(function () {
    'use strict';
    if (window.blLiveStarted || !window.blLiveConfig) return;
    window.blLiveStarted = true;
    var config = window.blLiveConfig, stopped = false, version = '', deferred = false;
    function replaceContents(target, source) {
        if (target && target.contains(document.activeElement)) { deferred = true; return; }
        if (!target || !source || target.querySelector('input,select,textarea,form') ||
            source.querySelector('input,select,textarea,form') || target.contains(document.activeElement)) return;
        if (target.innerHTML !== source.innerHTML) target.innerHTML = source.innerHTML;
        target.className = source.className;
    }
    function update(result) {
        if (stopped) return;
        if (result.changed === false) return;
        deferred = false;
        var nextDay = Number(result.currentDay);
        if (Number.isInteger(nextDay) && nextDay > Number(config.currentDay) && nextDay <= 34) {
            // Use only the navigation form, never submit unsaved tips (xform).
            window.submitaction('list');
            stopped = true;
            return;
        }
        var fresh = new DOMParser().parseFromString(result.html, 'text/html');
        var selectors = config.view === 'table' ? ['table.minitable[width="600"]'] :
            config.view === 'bltable' ? ['table.minitable'] : config.view === 'day' ? ['table.tablestyle[width="150"]'] : [];
        selectors.forEach(function (selector) {
            var sources = fresh.querySelectorAll(selector);
            document.querySelectorAll(selector).forEach(function (target, index) { replaceContents(target, sources[index]); });
        });
        if (config.view === 'day') {
            // Match rows by their two team names; only result and points cells change.
            // Never replace the row or its tip fields, even after kickoff.
            var rows = Array.from(fresh.querySelectorAll('tr')).filter(function (row) { return row.cells.length === 10; });
            document.querySelectorAll('tr').forEach(function (row) {
                if (row.cells.length !== 10) return;
                var source = rows.find(function (other) {
                    return other.cells[0].textContent.trim() === row.cells[0].textContent.trim() &&
                        other.cells[2].textContent.trim() === row.cells[2].textContent.trim();
                });
                if (source) [6, 8, 9].forEach(function (i) { replaceContents(row.cells[i], source.cells[i]); });
            });
        }
        var sidebar = document.querySelector('[data-live-sidebar]');
        if (sidebar && sidebar.contains(document.activeElement)) deferred = true;
        if (sidebar && !sidebar.contains(document.activeElement)) {
            var links = new Map(Array.from(sidebar.querySelectorAll('a')).map(function (link) {
                return [link.textContent.trim(), link.cloneNode(true)];
            }));
            var body = document.createElement('tbody');
            body.appendChild(sidebar.rows[0].cloneNode(true));
            result.clubs.forEach(function (club, index) {
                var row = body.insertRow(); row.className = index % 2 ? 'firstline' : 'secondline';
                [index + 1 + '.', club.strName, club.intPoint, club.diff].forEach(function (value, column) {
                    var cell = row.insertCell();
                    if (column === 1 && links.has(club.strName)) cell.appendChild(links.get(club.strName));
                    else cell.textContent = value;
                });
            });
            sidebar.replaceChildren(body);
        }
        document.getElementById('live-status').textContent = 'Stand automatisch aktualisiert: ' + new Date().toLocaleTimeString('de-DE');
        // Retry deferred regions after focus moves, rather than acknowledging stale content.
        if (!deferred) version = result.version || '';
    }
    async function refresh() {
        if (stopped) return;
        if (!document.hidden) {
            var controller = new AbortController();
            var deadline = setTimeout(function () { controller.abort(); }, 15000);
            try {
                var response = await fetch('live.php', { method: 'POST', cache: 'no-store', signal: controller.signal,
                    body: new URLSearchParams({user: config.user, view: config.view, day: config.day, version: version}) });
                if (response.status === 401) { stopped = true; return; }
                if (!response.ok) throw new Error('HTTP ' + response.status);
                update(await response.json());
            } catch (error) {
                document.getElementById('live-status').textContent = 'Aktualisierung derzeit nicht möglich – erneuter Versuch in einer Minute.';
            } finally { clearTimeout(deadline); }
        }
        if (!stopped) setTimeout(refresh, 60000);
    }
    // Only a confirmed matchday advancement triggers navigation; no synthetic activity.
    setTimeout(refresh, 60000);
    window.addEventListener('pagehide', function () { stopped = true; });
}());
