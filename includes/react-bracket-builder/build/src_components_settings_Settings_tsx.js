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
class Node {
  constructor(id, name, left, right, depth, parent_id) {
    this.id = id;
    this.name = name;
    this.left = left;
    this.right = right;
    this.depth = depth;
    this.parent_id = parent_id;
  }
}
class Round {
  constructor(id, name, depth, nodes) {
    this.id = id;
    this.name = name;
    this.depth = depth;
    this.nodes = nodes;
  }
}
const MatchBox = _ref => {
  let {
    ...props
  } = _ref;
  const node1 = props.node1;
  const node2 = props.node2;
  // This component renders the lines connecting two nodes representing a "game"
  // These should be evenly spaced in the column and grow according to the number of other matches in the round
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: props.className,
    style: {
      ...props.style
    }
  });
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
  const round = props.round;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round__header"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_2__["default"].Control, {
    type: "text",
    value: round.name
  }));
};
const FinalRound = props => {
  const round = props.round;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundHeader, {
    round: round
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round__body"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spacer, {
    grow: "2"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchBox, {
    className: "wpbb-final-match"
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Spacer, {
    grow: "2"
  })));
};
const RoundComponent = props => {
  const round = props.round;
  const direction = props.direction;
  const numDirections = props.numDirections;
  const matchHeight = props.matchHeight;

  // For a given round and it's depth, we know that the number of nodes in this round will be 2^depth
  // For example, a round with depth 1 has 2 nodes and a round at depth 3 can have up to 8 nodes
  // The number of matches in a round is the number of nodes / 2
  // However, each round component only renders the match in a given direction. So for a bracket with 2 directions, 
  // the number of matches is split in half

  const buildMatches = () => {
    const numMatches = 2 ** round.depth / 2 / numDirections;
    const className = direction === Direction.TopLeft || direction === Direction.BottomLeft ? 'wpbb-match-box-left' : 'wpbb-match-box-right';
    const matches = Array.from(Array(numMatches).keys()).map(i => {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchBox, {
        className: className,
        style: {
          height: matchHeight,
          marginBottom: i + 1 < numMatches ? matchHeight : 0
        }
      });
    });
    return matches;
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-round"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundHeader, {
    round: round
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
    console.log(num);
    setNumRounds(parseInt(num));
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    className: "wpbb-options-form"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Number of Rounds:"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: numRounds,
    onChange: handleChange
  }, options));
};
const Bracket = props => {
  const {
    numRounds
  } = props;
  const rounds = Array.from(Array(numRounds).keys()).map(i => {
    return new Round(i + 1, `Round ${numRounds - i}`, i + 1, []);
  });
  const targetHeight = 600;

  // The number of rounds sets the initial height of each match
  // const firstRoundMatchHeight = targetHeight / rounds.length / 2;
  const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 2) / 2;

  /**
   * Build rounds in two directions, left to right and right to left
   */
  const buildRounds2 = rounds => {
    // Assume rounds are sorted by depth
    // Rendering from left to right, sort by depth descending
    const reversed = rounds.slice(1).reverse();
    const numDirections = 2;
    return [...rounds.slice(1).reverse().map((round, idx) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round,
      direction: Direction.TopLeft,
      numDirections: numDirections,
      matchHeight: 2 ** idx * firstRoundMatchHeight
    })),
    // handle final round differently
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FinalRound, {
      round: rounds[0]
    }), ...rounds.slice(1).map((round, idx, arr) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round,
      direction: Direction.TopRight,
      numDirections: numDirections,
      matchHeight: 2 ** (arr.length - 1 - idx) * firstRoundMatchHeight
    }))];
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-bracket"
  }, buildRounds2(rounds));
};
const BracketModal = props => {
  const {
    show,
    handleCancel,
    handleSave
  } = props;
  const [numRounds, setNumRounds] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(4);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"], {
    className: "wpbb-bracket-modal",
    show: show,
    onHide: handleCancel,
    size: "xl",
    centered: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Header, {
    className: "wpbb-bracket-modal__header",
    closeButton: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Title, null, "Create Bracket"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(NumRoundsSelector, {
    numRounds: numRounds,
    setNumRounds: setNumRounds
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Body, {
    className: "pt-0"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Bracket, {
    numRounds: numRounds
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