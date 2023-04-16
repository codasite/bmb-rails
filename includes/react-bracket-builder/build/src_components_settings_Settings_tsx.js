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
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Col.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Container.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Row.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Button.js");
/* harmony import */ var react_bootstrap__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react-bootstrap */ "./node_modules/react-bootstrap/esm/Modal.js");




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
const RoundComponent = props => {
  const round = props.round;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_2__["default"], {
    style: {
      height: '100%',
      border: '1px solid black'
    }
  }, round.depth);
};
const Bracket = () => {
  const [rounds, setRounds] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)([new Round(1, 'Round 1', 0, []), new Round(2, 'Round 2', 1, []), new Round(3, 'Round 3', 2, []), new Round(4, 'Round 4', 3, [])]);
  const buildRounds = rounds => {
    // Assume rounds are sorted by depth
    // Rendering from left to right, sort by depth descending
    const reversed = rounds.slice(1).reverse();
    return [...reversed.map(round => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round
    })), ...rounds.map(round => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RoundComponent, {
      round: round
    }))];
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"], {
    style: {
      height: '600px'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_4__["default"], {
    style: {
      height: '100%'
    }
  }, buildRounds(rounds)));
};
const BracketModal = props => {
  const {
    show,
    handleCancel,
    handleSave
  } = props;
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_5__["default"], {
    show: show,
    onHide: handleCancel,
    size: "xl",
    centered: true,
    style: {
      zIndex: '99999999',
      position: 'relative'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_5__["default"].Header, {
    closeButton: true,
    style: {
      borderBottom: '0'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_5__["default"].Title, null, "Create Bracket")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_5__["default"].Body, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Bracket, null)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_5__["default"].Footer, {
    style: {
      borderTop: '0'
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_6__["default"], {
    variant: "secondary",
    onClick: handleCancel
  }, "Close"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_6__["default"], {
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