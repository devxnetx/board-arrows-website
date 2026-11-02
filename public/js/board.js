(function () {
  var boardEl = document.getElementById("board");
  var svg = document.getElementById("arrows");
  var statusEl = document.getElementById("status");
  if (!boardEl || typeof Chess !== "function" || typeof BAArrows === "undefined") {
    if (statusEl) statusEl.textContent = "The board scripts did not load.";
    return;
  }

  var START = 10 * 60 * 1000;
  var ARROWS = { multiPv: 3, continuationPlies: 3, continuationsOnAllLines: false };

  var game = new Chess();
  var human = "w";
  var elo = 1500;
  var eloMin = 1320;
  var eloMax = 3190;
  var whiteMs = START;
  var blackMs = START;
  var started = false;
  var over = null;
  var stack = [];
  var selected = "";
  var targets = [];
  var drag = null;
  var worker = null;
  var ready = false;
  var state = "idle";
  var next = null;
  var lines = new Map();
  var depthSeen = 0;
  var paintTimer = null;
  var tickAt = 0;

  var evalEl = document.getElementById("eval");
  var evalWhite = document.getElementById("eval-white");
  var evalScore = document.getElementById("eval-score");
  var movesEl = document.getElementById("moves");
  var promo = document.getElementById("promo");
  var result = document.getElementById("result");
  var resultTitle = document.getElementById("result-title");
  var resultDetail = document.getElementById("result-detail");
  var eloSelect = document.getElementById("elo");
  var revertBtn = document.getElementById("revert");
  var resignBtn = document.getElementById("resign");
  var clockTop = document.getElementById("clock-top");
  var clockBottom = document.getElementById("clock-bottom");
  var subTop = document.getElementById("sub-top");
  var subBottom = document.getElementById("sub-bottom");

  var ghost = document.createElement("div");
  ghost.className = "ghost";
  ghost.hidden = true;
  document.body.appendChild(ghost);

  function say(text) { statusEl.textContent = text; }

  function readElo() {
    var value = eloSelect.value;
    if (value === "full") return null;
    var n = Number(value);
    return isFinite(n) ? n : 1500;
  }

  function pieceUrl(color, type) {
    return "/pieces/cburnett/" + color + type.toUpperCase() + ".svg";
  }

  function pieceAt(square) {
    var file = square.charCodeAt(0) - 97;
    var rank = Number(square[1]) - 1;
    var row = game.board()[7 - rank];
    return row ? row[file] : null;
  }

  function kingSquare(color) {
    var rows = game.board();
    var rank;
    var file;
    for (rank = 0; rank < 8; rank++) {
      for (file = 0; file < 8; file++) {
        var piece = rows[rank][file];
        if (piece && piece.type === "k" && piece.color === color) {
          return "abcdefgh"[file] + String(8 - rank);
        }
      }
    }
    return "";
  }

  function lastMove() {
    var hist = game.history({ verbose: true });
    return hist.length ? hist[hist.length - 1] : null;
  }

  function fmt(ms) {
    ms = Math.max(0, ms);
    if (ms < 10000) return (ms / 1000).toFixed(1);
    var total = Math.ceil(ms / 1000);
    var minutes = Math.floor(total / 60);
    var seconds = total % 60;
    return minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
  }

  function renderClocks() {
    var topColor = human === "w" ? "b" : "w";
    var topMs = topColor === "w" ? whiteMs : blackMs;
    var bottomMs = human === "w" ? whiteMs : blackMs;
    clockTop.textContent = fmt(topMs);
    clockBottom.textContent = fmt(bottomMs);
    var running = started && !over;
    clockTop.classList.toggle("running", running && game.turn() === topColor);
    clockBottom.classList.toggle("running", running && game.turn() === human);
    clockTop.classList.toggle("low", topMs < 10000);
    clockBottom.classList.toggle("low", bottomMs < 10000);
  }

  function renderNames() {
    subTop.textContent = elo == null ? "Full strength" : String(elo);
    subBottom.textContent = human === "w" ? "White" : "Black";
    evalEl.classList.toggle("flipped", human === "b");
    document.getElementById("as-white").classList.toggle("on", human === "w");
    document.getElementById("as-black").classList.toggle("on", human === "b");
  }

  function humanHasMoved() {
    var hist = game.history({ verbose: true });
    var i;
    for (i = 0; i < hist.length; i++) {
      if (hist[i].color === human) return true;
    }
    return false;
  }

  function renderButtons() {
    revertBtn.disabled = !humanHasMoved();
    resignBtn.disabled = !!over || stack.length === 0;
  }

  function renderMoves() {
    var hist = game.history();
    movesEl.innerHTML = "";
    var i;
    for (i = 0; i < hist.length; i += 2) {
      var row = document.createElement("div");
      var num = document.createElement("span");
      num.textContent = String(i / 2 + 1) + ".";
      var white = document.createElement("span");
      white.textContent = hist[i];
      var black = document.createElement("span");
      black.textContent = hist[i + 1] || "";
      if (i === hist.length - 1) white.className = "on";
      if (i + 1 === hist.length - 1) black.className = "on";
      row.appendChild(num);
      row.appendChild(white);
      row.appendChild(black);
      movesEl.appendChild(row);
    }
    movesEl.scrollTop = movesEl.scrollHeight;
  }

  function render() {
    var files = human === "b" ? "hgfedcba" : "abcdefgh";
    var ranks = human === "b" ? "12345678" : "87654321";
    var prev = lastMove();
    var king = game.in_check() ? kingSquare(game.turn()) : "";
    boardEl.innerHTML = "";
    var rank;
    var file;
    for (rank = 0; rank < 8; rank++) {
      for (file = 0; file < 8; file++) {
        var square = files[file] + ranks[rank];
        var fileIndex = square.charCodeAt(0) - 97;
        var rankIndex = Number(square[1]) - 1;
        var button = document.createElement("button");
        button.type = "button";
        button.className = "sq " + ((fileIndex + rankIndex) % 2 === 0 ? "dark" : "light");
        button.dataset.square = square;
        if (square === selected) button.className += " selected";
        if (prev && (square === prev.from || square === prev.to)) button.className += " moved";
        if (king === square) button.className += " check";
        if (targets.indexOf(square) !== -1) {
          button.className += " target";
          if (pieceAt(square)) button.className += " capture";
        }
        if (file === 0) {
          var rankLabel = document.createElement("span");
          rankLabel.className = "coord rank";
          rankLabel.textContent = square[1];
          button.appendChild(rankLabel);
        }
        if (rank === 7) {
          var fileLabel = document.createElement("span");
          fileLabel.className = "coord file";
          fileLabel.textContent = square[0];
          button.appendChild(fileLabel);
        }
        var piece = pieceAt(square);
        if (piece) {
          var icon = document.createElement("span");
          icon.className = "piece";
          icon.style.backgroundImage = "url('" + pieceUrl(piece.color, piece.type) + "')";
          button.appendChild(icon);
        }
        boardEl.appendChild(button);
      }
    }
    renderMoves();
    renderClocks();
    renderNames();
    renderButtons();
  }

  function playMove(spec) {
    stack.push({ w: whiteMs, b: blackMs, started: started });
    var move = { from: spec.from, to: spec.to };
    if (spec.promotion) move.promotion = spec.promotion;
    var played = game.move(move);
    if (!played) {
      stack.pop();
      return null;
    }
    if (!started) {
      started = true;
      tickAt = performance.now();
    }
    return played;
  }

  function undoOne() {
    if (!game.history().length) return;
    game.undo();
    var snap = stack.pop();
    if (!snap) return;
    whiteMs = snap.w;
    blackMs = snap.b;
    started = snap.started;
  }

  function clearEngineView() {
    lines = new Map();
    depthSeen = 0;
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    if (paintTimer) {
      clearTimeout(paintTimer);
      paintTimer = null;
    }
  }

  function closePromo() {
    promo.hidden = true;
    promo.innerHTML = "";
  }

  function shortEval(line) {
    if (!line) return "0.0";
    if (line.mate != null && line.mate !== 0) return (line.mate > 0 ? "+M" : "-M") + Math.abs(line.mate);
    var pawns = (line.cp || 0) / 100;
    return (pawns > 0 ? "+" : "") + pawns.toFixed(1);
  }

  function packed() {
    var turn = game.turn();
    return Array.from(lines.values())
      .sort(function (a, b) { return a.multipv - b.multipv; })
      .map(function (info) {
        var score = BAUci.toWhite(info, turn);
        return { multipv: info.multipv, depth: info.depth, cp: score.cp, mate: score.mate, pv: info.pv };
      });
  }

  function paintLines() {
    var packedLines = packed();
    var best = packedLines[0];
    var share = BAUci.whiteShare(best);
    evalWhite.style.flexGrow = String(Math.max(0.01, share));
    evalEl.querySelector(".eval-rest").style.flexGrow = String(Math.max(0.01, 100 - share));
    evalScore.textContent = shortEval(best);
    BAArrows.draw(svg, BAArrows.arrowsFor(packedLines, ARROWS), human === "b", { showNumbers: false });
  }

  function schedulePaint() {
    if (paintTimer) return;
    paintTimer = setTimeout(function () {
      paintTimer = null;
      paintLines();
    }, 80);
  }

  function onInfo(info) {
    if (info.multipv > ARROWS.multiPv) return;
    if (info.depth < depthSeen) return;
    if (info.depth > depthSeen) {
      depthSeen = info.depth;
      lines = new Map();
    }
    lines.set(info.multipv, info);
    schedulePaint();
  }

  function strength(full) {
    if (full || elo == null) {
      worker.postMessage("setoption name UCI_LimitStrength value false");
      return;
    }
    var value = Math.max(eloMin, Math.min(eloMax, elo));
    worker.postMessage("setoption name UCI_LimitStrength value true");
    worker.postMessage("setoption name UCI_Elo value " + String(value));
  }

  function thinkMs() {
    if (elo == null) return 800;
    if (elo < 1600) return 280;
    if (elo < 2000) return 450;
    if (elo < 2400) return 600;
    if (elo < 2800) return 750;
    return 900;
  }

  function haltThen(fn) {
    next = fn;
    if (state === "idle") {
      var run = next;
      next = null;
      run();
      return;
    }
    if (state !== "stopping") {
      state = "stopping";
      worker.postMessage("stop");
    }
  }

  function beginAnalysis() {
    if (!ready) return;
    haltThen(function () {
      depthSeen = 0;
      lines = new Map();
      state = "analyze";
      strength(true);
      worker.postMessage("setoption name MultiPV value 3");
      worker.postMessage("position fen " + game.fen());
      worker.postMessage("go infinite");
      if (!over) {
        var text = "Your move. The arrows are full-strength Stockfish.";
        if (game.in_check()) text = "Check. " + text;
        say(text);
      }
    });
  }

  function beginMove() {
    if (!ready || over) return;
    if (game.turn() === human) {
      beginAnalysis();
      return;
    }
    haltThen(function () {
      if (over || game.turn() === human) {
        beginAnalysis();
        return;
      }
      state = "move";
      strength(false);
      worker.postMessage("setoption name MultiPV value 1");
      worker.postMessage("position fen " + game.fen());
      worker.postMessage("go movetime " + String(thinkMs()));
      say("Stockfish is thinking…");
      renderButtons();
    });
  }

  function finish(title, detail) {
    over = title;
    resultTitle.textContent = title;
    resultDetail.textContent = detail || "";
    result.hidden = false;
    say(detail ? title + ". " + detail + "." : title + ".");
    renderButtons();
    renderClocks();
    beginAnalysis();
  }

  function ended() {
    if (game.in_checkmate()) {
      finish(game.turn() === human ? "Stockfish wins" : "You win", "Checkmate");
      return true;
    }
    if (!game.in_draw()) return false;
    var why = "Draw";
    if (game.in_stalemate()) why = "Stalemate";
    else if (game.in_threefold_repetition()) why = "Threefold repetition";
    else if (game.insufficient_material()) why = "Insufficient material";
    else why = "Fifty-move rule";
    finish("Draw", why);
    return true;
  }

  function afterPlay() {
    clearEngineView();
    closePromo();
    selected = "";
    targets = [];
    render();
    if (ended()) return;
    if (game.turn() !== human) beginMove();
    else beginAnalysis();
  }

  function commit(spec) {
    if (over || game.turn() !== human) return;
    if (!playMove(spec)) {
      render();
      return;
    }
    afterPlay();
  }

  function applyUci(uci) {
    if (over) return;
    if (!uci || uci === "(none)" || uci.length < 4) {
      if (!ended()) beginAnalysis();
      return;
    }
    var played = playMove({
      from: uci.slice(0, 2),
      to: uci.slice(2, 4),
      promotion: uci.length > 4 ? uci[4] : "",
    });
    if (!played) {
      say("Stockfish returned a move that could not be played.");
      render();
      return;
    }
    afterPlay();
  }

  function openPromo(from, to) {
    closePromo();
    var file = to.charCodeAt(0) - 97;
    var rank = Number(to[1]) - 1;
    var visualFile = human === "b" ? 7 - file : file;
    var rankFromTop = human === "b" ? rank : 7 - rank;
    var growUp = rankFromTop > 4;
    var start = growUp ? rankFromTop - 3 : rankFromTop;
    promo.style.left = (visualFile * 12.5) + "%";
    promo.style.top = (start * 12.5) + "%";
    promo.hidden = false;
    var order = growUp ? ["n", "b", "r", "q"] : ["q", "r", "b", "n"];
    order.forEach(function (type) {
      var button = document.createElement("button");
      button.type = "button";
      button.className = "promo-piece";
      button.style.backgroundImage = "url('" + pieceUrl(human, type) + "')";
      button.addEventListener("click", function (event) {
        event.stopPropagation();
        commit({ from: from, to: to, promotion: type });
      });
      promo.appendChild(button);
    });
  }

  function tryMove(from, to) {
    var choices = game.moves({ square: from, verbose: true }).filter(function (move) {
      return move.to === to;
    });
    if (!choices.length) return;
    if (choices[0].promotion) {
      openPromo(from, to);
      return;
    }
    commit({ from: from, to: to });
  }

  function squareFromPoint(x, y) {
    var hidden = ghost.hidden;
    ghost.hidden = true;
    var el = document.elementFromPoint(x, y);
    ghost.hidden = hidden;
    var square = el && el.closest ? el.closest("[data-square]") : null;
    return square ? square.dataset.square : "";
  }

  function showGhost(square, x, y) {
    var piece = pieceAt(square);
    if (!piece) return;
    var size = boardEl.clientWidth / 8;
    ghost.hidden = false;
    ghost.style.width = size + "px";
    ghost.style.height = size + "px";
    ghost.style.backgroundImage = "url('" + pieceUrl(piece.color, piece.type) + "')";
    ghost.style.transform = "translate(" + (x - size / 2) + "px, " + (y - size / 2) + "px)";
  }

  function hideGhost() {
    ghost.hidden = true;
  }

  function canMove() {
    return ready && !over && state !== "move" && state !== "stopping" && game.turn() === human;
  }

  boardEl.addEventListener("pointerdown", function (event) {
    if (event.button !== 0) return;
    if (!promo.hidden) {
      closePromo();
      return;
    }
    var squareEl = event.target.closest("[data-square]");
    if (!squareEl || !canMove()) return;
    event.preventDefault();
    var square = squareEl.dataset.square;
    var piece = pieceAt(square);
    if (piece && piece.color === human) {
      selected = square;
      targets = game.moves({ square: square, verbose: true }).map(function (move) { return move.to; });
      drag = { from: square, x: event.clientX, y: event.clientY, moved: false, pointer: event.pointerId };
      render();
      boardEl.setPointerCapture(event.pointerId);
      return;
    }
    if (selected && targets.indexOf(square) !== -1) {
      drag = { from: selected, x: event.clientX, y: event.clientY, moved: false, pointer: event.pointerId, click: square };
      boardEl.setPointerCapture(event.pointerId);
    } else {
      selected = "";
      targets = [];
      render();
    }
  });

  boardEl.addEventListener("pointermove", function (event) {
    if (!drag || event.pointerId !== drag.pointer) return;
    if (Math.hypot(event.clientX - drag.x, event.clientY - drag.y) > 6) drag.moved = true;
    if (!drag.moved) return;
    showGhost(drag.from, event.clientX, event.clientY);
    var origin = boardEl.querySelector('[data-square="' + drag.from + '"] .piece');
    if (origin) origin.style.opacity = "0.35";
  });

  boardEl.addEventListener("pointercancel", function () {
    drag = null;
    hideGhost();
    render();
  });

  boardEl.addEventListener("pointerup", function (event) {
    if (!drag || event.pointerId !== drag.pointer) return;
    var from = drag.from;
    var moved = drag.moved;
    var click = drag.click || "";
    drag = null;
    hideGhost();
    var to = moved ? squareFromPoint(event.clientX, event.clientY) : click;
    if (to && targets.indexOf(to) !== -1) tryMove(from, to);
    else if (moved) render();
  });

  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") return;
    selected = "";
    targets = [];
    closePromo();
    hideGhost();
    render();
  });

  function newGame(color) {
    human = color || human;
    game.reset();
    stack = [];
    over = null;
    started = false;
    whiteMs = START;
    blackMs = START;
    selected = "";
    targets = [];
    closePromo();
    result.hidden = true;
    clearEngineView();
    evalWhite.style.flexGrow = "50";
    evalEl.querySelector(".eval-rest").style.flexGrow = "50";
    evalScore.textContent = "0.0";
    render();
    say(ready ? "Your move. The arrows are full-strength Stockfish." : "Loading Stockfish…");
    if (!ready) return;
    if (game.turn() !== human) beginMove();
    else beginAnalysis();
  }

  function revert() {
    if (!stack.length) return;
    haltThen(function () {
      over = null;
      result.hidden = true;
      closePromo();
      selected = "";
      targets = [];
      var guard = 0;
      while (game.history().length && (!lastMove() || lastMove().color !== human) && guard < 3) {
        undoOne();
        guard += 1;
      }
      if (game.history().length && lastMove() && lastMove().color === human) undoOne();
      clearEngineView();
      render();
      if (game.turn() !== human) beginMove();
      else beginAnalysis();
    });
  }

  function resign() {
    if (over || !stack.length) return;
    finish("You resigned", "Stockfish wins");
  }

  function noteElo(line) {
    var min = /min (\d+)/.exec(line);
    var max = /max (\d+)/.exec(line);
    if (!min || !max) return;
    eloMin = Number(min[1]);
    eloMax = Number(max[1]);
    Array.from(eloSelect.options).forEach(function (option) {
      if (option.value === "full") return;
      var n = Number(option.value);
      option.hidden = n < eloMin || n > eloMax;
    });
    if (elo != null && (elo < eloMin || elo > eloMax)) {
      eloSelect.value = "full";
      elo = null;
    }
  }

  function onBestmove(line) {
    if (state === "stopping" || state === "analyze") {
      var run = next;
      next = null;
      state = "idle";
      if (run) run();
      return;
    }
    if (state === "move") {
      state = "idle";
      applyUci(line.split(/\s+/)[1]);
    }
  }

  function boot() {
    worker = new Worker("/engine/stockfish-19-lite-single.js");
    worker.onmessage = function (event) {
      var line = typeof event.data === "string" ? event.data : "";
      if (!line) return;
      if (line.indexOf("option name UCI_Elo ") === 0) noteElo(line);
      if (line === "uciok") {
        worker.postMessage("setoption name Hash value 16");
        worker.postMessage("isready");
        return;
      }
      if (line === "readyok") {
        if (!ready) {
          ready = true;
          newGame(human);
        }
        return;
      }
      if (state === "analyze" && line.indexOf("info ") === 0) {
        var info = BAUci.parseInfo(line);
        if (info) onInfo(info);
        return;
      }
      if (line.indexOf("bestmove") === 0) onBestmove(line);
    };
    worker.onerror = function () { say("Stockfish did not start in this browser."); };
    worker.postMessage("uci");
  }

  function tick() {
    var now = performance.now();
    if (!started || over) {
      tickAt = now;
      return;
    }
    var dt = now - tickAt;
    tickAt = now;
    if (game.turn() === "w") whiteMs -= dt;
    else blackMs -= dt;
    if (whiteMs <= 0) {
      whiteMs = 0;
      finish(human === "w" ? "You lost on time" : "Stockfish lost on time", "Time");
    } else if (blackMs <= 0) {
      blackMs = 0;
      finish(human === "b" ? "You lost on time" : "Stockfish lost on time", "Time");
    }
    renderClocks();
  }

  elo = readElo();
  document.getElementById("as-white").addEventListener("click", function () { newGame("w"); });
  document.getElementById("as-black").addEventListener("click", function () { newGame("b"); });
  document.getElementById("new-game").addEventListener("click", function () { newGame(human); });
  document.getElementById("result-new").addEventListener("click", function () { newGame(human); });
  revertBtn.addEventListener("click", revert);
  resignBtn.addEventListener("click", resign);
  eloSelect.addEventListener("change", function () {
    elo = readElo();
    renderNames();
    if (state === "move") beginMove();
  });

  render();
  setInterval(tick, 100);
  boot();
})();
