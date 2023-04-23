"use strict";
(self["webpackChunkreact_bracket_builder"] = self["webpackChunkreact_bracket_builder"] || []).push([["src_components_settings_Settings_tsx"],{

/***/ "./src/components/bracket_builder/Bracket.tsx":
/*!****************************************************!*\
  !*** ./src/components/bracket_builder/Bracket.tsx ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Bracket": function() { return /* binding */ Bracket; },
/* harmony export */   "BracketModal": function() { return /* binding */ BracketModal; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Button.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Modal.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Form.js");






// Direction enum
var Direction = /*#__PURE__*/function (Direction) {
  Direction[Direction["TopLeft"] = 0] = "TopLeft";
  Direction[Direction["TopRight"] = 1] = "TopRight";
  Direction[Direction["BottomLeft"] = 2] = "BottomLeft";
  Direction[Direction["BottomRight"] = 3] = "BottomRight";
  return Direction;
}(Direction || {});
class Team {
  // constructor(id: number, name: string) {
  // 	this.id = id;
  // 	this.name = name;
  // }
  constructor(name) {
    this.name = name;
  }
}
class MatchNode {
  leftTeam = null;
  rightTeam = null;
  result = null;
  left = null;
  right = null;
  parent = null;
  constructor(parent, depth) {
    this.depth = depth;
    this.parent = parent;
  }
}
class Round {
  constructor(id, name, depth, roundNum, numMatches) {
    this.id = id;
    this.name = name;
    this.depth = depth;
    this.roundNum = roundNum;
    this.numMatches = numMatches;
    this.matches = [];
  }
}
class MatchTree {
  constructor(numRounds, numWildcards) {
    this.rounds = this.buildRounds(numRounds, numWildcards);
    // this.root = this.buildMatch(null, 0)
  }

  buildRounds(numRounds, numWildcards) {
    // The number of matches in a round is equal to 2^depth unless it's the first round
    // and there are wildcards. In that case, the number of matches equals the number of wildcards
    const rootMatch = new MatchNode(null, 0);
    const finalRound = new Round(1, 'Round 1', 0, numRounds, 1);
    finalRound.matches = [rootMatch];
    const rounds = [finalRound];
    for (let i = 1; i < numRounds; i++) {
      console.log('build round ', i);
      const numMatches = i === numRounds - 1 && numWildcards > 0 ? numWildcards : 2 ** i;
      const round = new Round(i + 1, `Round ${numRounds - i}`, i, numRounds - i, numMatches);
      const matches = [];
      const maxMatches = 2 ** i;
      for (let x = 0; x < maxMatches; x++) {
        const parentIndex = Math.floor(x / 2);
        const parent = rounds[i - 1].matches[parentIndex];
        const match = new MatchNode(parent, i);
        // If x is even, match is the left child of parent, otherwise right child
        const leftChild = x % 2 === 0;
        if (leftChild) {
          parent.left = match;
        } else {
          parent.right = match;
        }
        matches[x] = match;
      }
      round.matches = matches;
      rounds[i] = round;
    }
    ;
    rounds.forEach(round => {
      console.log(round.matches);
    });
    return rounds;
  }
  buildMatch(parent, depth) {
    if (depth >= this.rounds.length) {
      return null;
    }
    const match = new MatchNode(parent, depth);

    // Give the round at this depth a reference to the match node
    // Matches are ordered left to right 
    const round = this.rounds[depth];
    round.matches.push(match);
    match.left = this.buildMatch(match, depth + 1);
    match.right = this.buildMatch(match, depth + 1);
    return match;
  }
}
const TeamSlot = props => {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: props.className
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "wpbb-team-name"
  }, "Michigan State"));
};
const MatchBox = _ref => {
  let {
    ...props
  } = _ref;
  const node1 = props.node1;
  const node2 = props.node2;
  const empty = props.empty;
  const direction = props.direction;
  const upperOuter = props.upperOuter;
  const lowerOuter = props.lowerOuter;
  const height = props.height;
  const spacing = props.spacing;
  if (empty) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "wpbb-match-box-empty",
      style: {
        height: height + spacing
      }
    });
  }
  let className;
  if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
    // Left side of the bracket
    className = 'wpbb-match-box-left';
  } else {
    // Right side of the bracket
    className = 'wpbb-match-box-right';
  }
  if (upperOuter && lowerOuter) {
    // First round
    className += '-outer';
  } else if (upperOuter) {
    // Upper bracket
    className += '-outer-upper';
  } else if (lowerOuter) {
    // Lower bracket
    className += '-outer-lower';
  }

  // This component renders the lines connecting two nodes representing a "game"
  // These should be evenly spaced in the column and grow according to the number of other matches in the round
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: className,
    style: {
      height: height,
      marginBottom: spacing
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TeamSlot, {
    className: "wpbb-team1"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TeamSlot, {
    className: "wpbb-team2"
  }));
};
const Spacer = _ref2 => {
  let {
    grow = '1'
  } = _ref2;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      flexGrow: grow
    }
  });
};
const RoundHeader = props => {
  const [editRoundName, setEditRoundName] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [nameBuffer, setNameBuffer] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const round = props.round;
  const updateRoundName = props.updateRoundName;
  (0,react__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    setNameBuffer(props.round.name);
  }, [props.round.name]);
  const doneEditing = () => {
    setEditRoundName(false);
    props.updateRoundName(props.round.id, nameBuffer);
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round__header"
  }, editRoundName ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_2__["default"].Control, {
    type: "text",
    value: nameBuffer,
    autoFocus: true,
    onFocus: e => e.target.select(),
    onBlur: () => doneEditing(),
    onChange: e => setNameBuffer(e.target.value),
    onKeyUp: e => {
      if (e.key === 'Enter') {
        doneEditing();
      }
    }
  }) : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    onClick: () => setEditRoundName(true)
  }, round.name));
};
const FinalRound = props => {
  const round = props.round;
  const updateRoundName = props.updateRoundName;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundHeader, {
    round: round,
    updateRoundName: updateRoundName
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round__body"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spacer, {
    grow: "2"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-final-match"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TeamSlot, {
    className: "wpbb-team1"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TeamSlot, {
    className: "wpbb-team2"
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spacer, {
    grow: "2"
  })));
};
const RoundComponent = props => {
  const round = props.round;
  const direction = props.direction;
  const numDirections = props.numDirections;
  const matchHeight = props.matchHeight;
  const updateRoundName = props.updateRoundName;

  // For a given round and it's depth, we know that the number of nodes in this round will be 2^depth
  // For example, a round with depth 1 has 2 nodes and a round at depth 3 can have up to 8 nodes
  // The number of matches in a round is the number of nodes / 2
  // However, each round component only renders the match in a given direction. So for a bracket with 2 directions, 
  // the number of matches is split in half

  const buildMatches = () => {
    // const numMatches = 2 ** round.depth / 2 / numDirections
    // Get the number of matches in a single direction (left or right)
    const numMatches = round.numMatches / numDirections;
    // Get the difference between the specified number of matches and how many there could possibly be
    // This is to account for wildcard rounds where there are less than the maximum number of matches
    const maxMatches = 2 ** (round.depth + 1) / 2 / numDirections;
    const emptyMatches = maxMatches - numMatches;

    // console.log('round numMatches', roundNumMatches)

    // Whether there are any matches below this round
    // Used to determine whether to truncate the match box border so that it does not extend past the team slot
    // const outerRound = round.roundNum === 1
    let upperOuter = false;
    let lowerOuter = false;
    if (round.roundNum === 1) {
      upperOuter = true;
      lowerOuter = true;
    }
    const matches = Array.from(Array(maxMatches).keys()).map(i => {
      return (
        // <MatchBox className={className} style={{ height: matchHeight, marginBottom: (i + 1 < numMatches ? matchHeight : 0) }} />
        (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchBox, {
          empty: i < emptyMatches,
          direction: direction,
          upperOuter: upperOuter,
          lowerOuter: lowerOuter,
          height: matchHeight,
          spacing: i + 1 < maxMatches ? matchHeight : 0 // Do not add spacing to the last match in the round column
        })
      );
    });

    return matches;
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundHeader, {
    round: round,
    updateRoundName: updateRoundName
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round__body"
  }, buildMatches()));
};
const NumRoundsSelector = props => {
  const {
    numRounds,
    setNumRounds
  } = props;
  const minRounds = 1;
  const maxRounds = 6;
  const options = Array.from(Array(maxRounds - minRounds + 1).keys()).map(i => {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: i + minRounds
    }, i + minRounds);
  });
  const handleChange = event => {
    const num = event.target.value;
    setNumRounds(parseInt(num));
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-option-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Number of Rounds:"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: numRounds,
    onChange: handleChange
  }, options));
};
const NumWildcardsSelector = props => {
  const {
    numWildcards,
    setNumWildcards,
    maxWildcards
  } = props;
  const minWildcards = 0;

  // Number of wildcards must be an even number or 0
  let options = [(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: 0
  }, "0")];
  options = [...options, ...Array.from(Array(maxWildcards / 2).keys()).reverse().map(i => {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: (i + 1) * 2
    }, (i + 1) * 2);
  })];
  const handleChange = event => {
    const num = event.target.value;
    setNumWildcards(parseInt(num));
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-option-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Wildcard Games:"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: numWildcards,
    onChange: handleChange
  }, options));
};
const Bracket = props => {
  const {
    numRounds,
    numWildcards
  } = props;
  const [rounds, setRounds] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const updateRoundName = (roundId, name) => {
    const newRounds = rounds.map(round => {
      if (round.id === roundId) {
        round.name = name;
      }
      return round;
    });
    setRounds(newRounds);
  };
  (0,react__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    const matchTree = new MatchTree(numRounds, numWildcards);
    setRounds(matchTree.rounds);
    // setRounds(buildRounds(numRounds, numWildcards))
  }, [numRounds, numWildcards]);
  const targetHeight = 700;

  // The number of rounds sets the initial height of each match
  // const firstRoundMatchHeight = targetHeight / rounds.length / 2;
  const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 2) / 2;

  /**
   * Build rounds in two directions, left to right and right to left
   */
  const buildRounds2 = rounds => {
    // Assume rounds are sorted by depth
    // Rendering from left to right, sort by depth descending
    const numDirections = 2;
    return [...rounds.slice(1).reverse().map((round, idx) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round,
      direction: Direction.TopLeft,
      numDirections: numDirections,
      matchHeight: 2 ** idx * firstRoundMatchHeight,
      updateRoundName: updateRoundName
    })),
    // handle final round differently
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FinalRound, {
      round: rounds[0],
      updateRoundName: updateRoundName
    }), ...rounds.slice(1).map((round, idx, arr) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round,
      direction: Direction.TopRight,
      numDirections: numDirections,
      matchHeight: 2 ** (arr.length - 1 - idx) * firstRoundMatchHeight,
      updateRoundName: updateRoundName
    }))];
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-bracket"
  }, rounds.length > 0 && buildRounds2(rounds));
};
const BracketModal = props => {
  const {
    show,
    handleCancel,
    handleSave
  } = props;
  const [numRounds, setNumRounds] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(4);
  const [numWildcards, setNumWildcards] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(0);
  // The max number of wildcards is 2 less than the possible number of matches in the first round
  // (2^numRounds - 2)
  const maxWildcards = 2 ** (numRounds - 1) - 2;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"], {
    className: "wpbb-bracket-modal",
    show: show,
    onHide: handleCancel,
    size: "xl",
    centered: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Header, {
    className: "wpbb-bracket-modal__header",
    closeButton: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Title, null, "Create Bracket"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    className: "wpbb-options-form"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(NumRoundsSelector, {
    numRounds: numRounds,
    setNumRounds: setNumRounds
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(NumWildcardsSelector, {
    numWildcards: numWildcards,
    setNumWildcards: setNumWildcards,
    maxWildcards: maxWildcards
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Body, {
    className: "pt-0"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Bracket, {
    numRounds: numRounds,
    numWildcards: numWildcards
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Footer, {
    className: "wpbb-bracket-modal__footer"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_4__["default"], {
    variant: "secondary",
    onClick: handleCancel
  }, "Close"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_4__["default"], {
    variant: "primary",
    onClick: handleSave
  }, "Save Changes")));
};

/***/ }),

/***/ "./src/components/settings/Settings.tsx":
/*!**********************************************!*\
  !*** ./src/components/settings/Settings.tsx ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var react_bootstrap_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-bootstrap/Button */ "./node_modules/react-bootstrap/esm/Button.js");
/* harmony import */ var _bracket_builder_Bracket__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../bracket_builder/Bracket */ "./src/components/bracket_builder/Bracket.tsx");




class Sport {
  constructor(id, name, teams) {
    this.id = id;
    this.name = name;
    this.teams = teams;
  }
}
class Team {
  constructor(id, name) {
    this.id = id;
    this.name = name;
  }
}
const Settings = () => {
  const [showBracketModal, setShowBracketModal] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const handleCloseBracketModal = () => setShowBracketModal(false);
  const handleSaveBracketModal = () => setShowBracketModal(false);
  const handleShowBracketModal = () => setShowBracketModal(true);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    className: "mt-4"
  }, "Bracket Builder Settings"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap_Button__WEBPACK_IMPORTED_MODULE_3__["default"], {
    variant: "primary",
    className: "mt-6",
    onClick: handleShowBracketModal
  }, "Save"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_bracket_builder_Bracket__WEBPACK_IMPORTED_MODULE_2__.BracketModal, {
    show: showBracketModal,
    handleCancel: handleCloseBracketModal,
    handleSave: handleSaveBracketModal
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_bracket_builder_Bracket__WEBPACK_IMPORTED_MODULE_2__.Bracket, null));
};
class BracketBuilderApi {
  // static _sportsApi: SportsApi;
  // static _bracketApi: BracketApi;
  constructor() {
    // @ts-ignore
    this.url = wpbb_ajax_obj.rest_url;
  }
  // static getBracketApi() {
  // 	if (!BracketBuilderApi._bracketApi) {
  // 		// @ts-ignore
  // 		BracketBuilderApi._bracketApi = new BracketApi();
  // 	}
  // 	return BracketBuilderApi._bracketApi;
  // }

  // static getSportsApi() {
  // 	if (!BracketBuilderApi._sportsApi) {
  // 		// @ts-ignore
  // 		BracketBuilderApi._sportsApi = new SportsApi();
  // 	}
  // 	return BracketBuilderApi._sportsApi;
  // }
  async performRequest(path, method, body) {
    const response = await fetch(`${this.url}${path}`, {
      method,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });
    return response.json();
  }
}
class BracketApi extends BracketBuilderApi {
  path = 'brackets';
  async getBrackets() {
    return await this.performRequest(this.path, 'GET', {});
  }
}

// class SportsApi extends BracketBuilderApi {
// 	path: string = 'sports';
// 	async getSports() {
// 		return await this.performRequest(this.path, 'GET', {});
// 	}
// }
// SportsApi.getInstance().getSports().then((sports) => {
// 	console.log(sports)
// })
const fetchBrackets = async () => {
  // @ts-ignore
  const res = await fetch(`${wpbb_ajax_obj.rest_url}brackets`);
  const brackets = await res.json();
  console.log(brackets);
};
fetchBrackets();
/* harmony default export */ __webpack_exports__["default"] = (Settings);

/***/ })

}]);
//# sourceMappingURL=src_components_settings_Settings_tsx.js.map