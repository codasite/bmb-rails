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





var WildcardPlacement = /*#__PURE__*/function (WildcardPlacement) {
  WildcardPlacement[WildcardPlacement["Top"] = 0] = "Top";
  WildcardPlacement[WildcardPlacement["Bottom"] = 1] = "Bottom";
  WildcardPlacement[WildcardPlacement["Center"] = 2] = "Center";
  WildcardPlacement[WildcardPlacement["Split"] = 3] = "Split";
  return WildcardPlacement;
}(WildcardPlacement || {}); // Direction enum
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
    this.name = 'Packers';
  }
  clone() {
    return new Team(this.name);
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
  clone() {
    const match = this;
    const clone = new MatchNode(null, match.depth);
    clone.leftTeam = match.leftTeam ? match.leftTeam.clone() : null;
    clone.rightTeam = match.rightTeam ? match.rightTeam.clone() : null;
    if (match.result) {
      if (match.result === match.leftTeam) {
        clone.result = clone.leftTeam;
      } else if (match.result === match.rightTeam) {
        clone.result = clone.rightTeam;
      }
    }
    return clone;
  }
}
class Round {
  constructor(id, name, depth, roundNum) {
    this.id = id;
    this.name = name;
    this.depth = depth;
    this.roundNum = roundNum;
    this.matches = [];
  }
}
class WildcardRange {
  constructor(min, max) {
    this.min = min;
    this.max = max;
  }
  toString() {
    return `${this.min}-${this.max}`;
  }
}
class MatchTree {
  constructor(numRounds, numWildcards, wildcardsPlacement) {
    this.rounds = this.buildRounds(numRounds, numWildcards, wildcardsPlacement);
  }
  buildRounds(numRounds, numWildcards, wildcardPlacement) {
    // The number of matches in a round is equal to 2^depth unless it's the first round
    // and there are wildcards. In that case, the number of matches equals the number of wildcards
    const rootMatch = new MatchNode(null, 0);
    const finalRound = new Round(1, `Round ${numRounds}`, 0, numRounds);
    finalRound.matches = [rootMatch];
    const rounds = [finalRound];
    for (let i = 1; i < numRounds; i++) {
      let ranges = [];
      if (i === numRounds - 1 && numWildcards > 0) {
        // const placement = WildcardPlacement.Top
        const placement = wildcardPlacement;
        const maxNodes = 2 ** i;
        const range1 = this.getWildcardRange(0, maxNodes / 2, numWildcards / 2, placement);
        const range2 = this.getWildcardRange(maxNodes / 2, maxNodes, numWildcards / 2, placement);
        ranges = [...range1, ...range2];
      }
      const round = new Round(i + 1, `Round ${numRounds - i}`, i, numRounds - i);
      const maxMatches = 2 ** i;
      const matches = [];
      for (let x = 0; x < maxMatches; x++) {
        if (ranges.length > 0) {
          // check to see if x is in the range of any of the wildcard ranges
          const inRange = ranges.some(range => {
            return x >= range.min && x < range.max;
          });
          if (!inRange) {
            matches[x] = null;
            continue;
          }
        }
        // const parentIndex = Math.floor(x / 2)
        // const parent = rounds[i - 1].matches[parentIndex]
        const parent = this.getParent(x, i, rounds);
        const match = new MatchNode(parent, i);
        this.assignMatchToParent(x, match, parent);
        matches[x] = match;
      }
      round.matches = matches;
      rounds[i] = round;
    }
    ;
    return rounds;
  }
  getWildcardRange(start, end, count, placement) {
    switch (placement) {
      case WildcardPlacement.Top:
        return [new WildcardRange(start, start + count)];
      case WildcardPlacement.Bottom:
        return [new WildcardRange(end - count, end)];
      case WildcardPlacement.Center:
        const offset = (end - start - count) / 2;
        return [new WildcardRange(start + offset, end - offset)];
      case WildcardPlacement.Split:
        return [new WildcardRange(start, start + count / 2), new WildcardRange(end - count / 2, end)];
    }
  }
  clone() {
    const tree = this;
    const newTree = new MatchTree(0, 0, WildcardPlacement.Center);
    // First, create the new rounds.
    newTree.rounds = tree.rounds.map(round => {
      const newRound = new Round(round.id, round.name, round.depth, round.roundNum);
      return newRound;
    });
    // Then, iterate over the new rounds to create the matches and update their parent relationships.
    newTree.rounds.forEach((round, roundIndex) => {
      round.matches = tree.rounds[roundIndex].matches.map((match, matchIndex) => {
        if (match === null) {
          return null;
        }
        const newMatch = match.clone();
        const parent = this.getParent(matchIndex, roundIndex, newTree.rounds);
        newMatch.parent = parent;
        this.assignMatchToParent(matchIndex, newMatch, parent);
        return newMatch;
      });
    });
    return newTree;
  }
  // 	newTree.rounds = tree.rounds.map((round, i, rounds) => {
  // 		const newRound = new Round(round.id, round.name, round.depth, round.roundNum)
  // 		console.log('i', i)
  // 		console.log('rounds', rounds)
  // 		newRound.matches = round.matches.map((match, x, matches) => {
  // 			if (match === null) {
  // 				return null
  // 			}
  // 			const newMatch = match.clone()
  // 			console.log('x', x)
  // 			console.log('matches', matches)
  // 			const parent = this.getParent(x, i, newTree.rounds)
  // 			newMatch.parent = parent
  // 			this.assignMatchToParent(x, newMatch, parent)
  // 			return newMatch
  // 		})
  // 		return newRound
  // 	})
  // 	return newTree
  // }

  getParent(matchIndex, roundIndex, rounds) {
    if (roundIndex === 0) {
      return null;
    }
    const parentIndex = Math.floor(matchIndex / 2);
    return rounds[roundIndex - 1].matches[parentIndex];
  }
  assignMatchToParent(matchIndex, match, parent) {
    if (parent === null) {
      return;
    }
    if (matchIndex % 2 === 0) {
      parent.left = match;
    } else {
      parent.right = match;
    }
  }
}
const TeamSlot = props => {
  const team = props.team;
  const updateTeam = props.updateTeam;
  const handleUpdateTeam = e => {
    e.stopPropagation();
    updateTeam('hi');
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: props.className,
    onClick: e => handleUpdateTeam(e)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "wpbb-team-name"
  }, team ? team.name : ''));
};
const MatchBox = props => {
  const match = props.match;
  const direction = props.direction;
  const height = props.height;
  const spacing = props.spacing;
  // const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
  const updateTeam = props.updateTeam;

  // const updateTeam = (name: string, left: boolean) => {
  // 	console.log('updateTeam', name)
  // 	// updateTeam(match.roundId, match.matchIndex, left, name)
  // }

  if (match === null) {
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
  const upperOuter = match.left === null;
  const lowerOuter = match.right === null;
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
    className: "wpbb-team1",
    team: match.leftTeam,
    updateTeam: name => updateTeam(true, name)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TeamSlot, {
    className: "wpbb-team2",
    team: match.rightTeam,
    updateTeam: name => updateTeam(false, name)
  }));
};
const Spacer = _ref => {
  let {
    grow = '1'
  } = _ref;
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
const MatchColumn = props => {
  const round = props.round;
  const matches = props.matches;
  const direction = props.direction;
  const matchHeight = props.matchHeight;
  const updateRoundName = props.updateRoundName;
  const updateTeam = props.updateTeam;
  // const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {

  const buildMatches = () => {
    const matchBoxes = matches.map((match, i) => {
      const matchIndex = direction === Direction.TopLeft || direction === Direction.BottomLeft ? i : i + matches.length;
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchBox, {
        match: match,
        direction: direction,
        height: matchHeight,
        spacing: i + 1 < matches.length ? matchHeight : 0 // Do not add spacing to the last match in the round column
        ,
        updateTeam: (left, name) => updateTeam(round.id, matchIndex, left, name)
      });
    });
    return matchBoxes;
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
const WildcardPlacementSelector = props => {
  const {
    wildcardPlacement,
    setWildcardPlacement
  } = props;
  const options = [(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: WildcardPlacement.Bottom
  }, "Bottom"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: WildcardPlacement.Top
  }, "Top"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: WildcardPlacement.Split
  }, "Split"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: WildcardPlacement.Center
  }, "Centered")];
  const handleChange = event => {
    const num = event.target.value;
    setWildcardPlacement(parseInt(num));
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wpbb-option-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Wildcard Placement:"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: wildcardPlacement,
    onChange: handleChange
  }, options));
};
const Bracket = props => {
  const {
    numRounds,
    numWildcards,
    wildcardPlacement
  } = props;
  // const [rounds, setRounds] = useState<Round[]>([])
  const [matchTree, setMatchTree] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(new MatchTree(numRounds, numWildcards, wildcardPlacement));
  const rounds = matchTree.rounds;

  // const updateRoundName = (roundId: number, name: string) => {
  // 	const newRounds = rounds.map((round) => {
  // 		if (round.id === roundId) {
  // 			round.name = name
  // 		}
  // 		return round
  // 	})
  // 	// setRounds(newRounds)
  // }
  const updateRoundName = (roundId, name) => {
    const newMatchTree = matchTree.clone();
    const roundToUpdate = newMatchTree.rounds.find(round => round.id === roundId);
    if (roundToUpdate) {
      roundToUpdate.name = name;
      setMatchTree(newMatchTree);
    }
  };

  // const updateTeam = ()

  const updateTeam = (roundId, matchIndex, left, name) => {
    const newMatchTree = matchTree.clone();
    const roundToUpdate = newMatchTree.rounds.find(round => round.id === roundId);
    if (roundToUpdate) {
      const matchToUpdate = roundToUpdate.matches[matchIndex];
      if (matchToUpdate) {
        if (left) {
          const team = matchToUpdate.leftTeam;
          if (team) {
            team.name = name;
          } else {
            matchToUpdate.leftTeam = new Team(name);
          }
        } else {
          const team = matchToUpdate.rightTeam;
          if (team) {
            team.name = name;
          } else {
            matchToUpdate.rightTeam = new Team(name);
          }
        }
      }
      setMatchTree(newMatchTree);
    }
  };
  (0,react__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    const matchTree = new MatchTree(numRounds, numWildcards, wildcardPlacement);
    // setRounds(matchTree.rounds)
    setMatchTree(matchTree);
    // setRounds(buildRounds(numRounds, numWildcards))
  }, [numRounds, numWildcards, wildcardPlacement]);
  const targetHeight = 800;

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
    return [...rounds.slice(1).reverse().map((round, idx) => {
      // Get the first half of matches for this column
      const colMatches = round.matches.slice(0, round.matches.length / 2);
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchColumn, {
        matches: colMatches,
        round: round,
        direction: Direction.TopLeft,
        numDirections: numDirections,
        matchHeight: 2 ** idx * firstRoundMatchHeight,
        updateRoundName: updateRoundName,
        updateTeam: updateTeam
      });
    }),
    // handle final round differently
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(FinalRound, {
      round: rounds[0],
      updateRoundName: updateRoundName
    }), ...rounds.slice(1).map((round, idx, arr) => {
      // Get the second half of matches for this column
      const colMatches = round.matches.slice(round.matches.length / 2);
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MatchColumn, {
        round: round,
        matches: colMatches,
        direction: Direction.TopRight,
        numDirections: numDirections,
        matchHeight: 2 ** (arr.length - 1 - idx) * firstRoundMatchHeight,
        updateRoundName: updateRoundName,
        updateTeam: updateTeam
      });
    })];
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
  const [wildcardPlacement, setWildcardPlacement] = (0,react__WEBPACK_IMPORTED_MODULE_1__.useState)(WildcardPlacement.Bottom);
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
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(WildcardPlacementSelector, {
    wildcardPlacement: wildcardPlacement,
    setWildcardPlacement: setWildcardPlacement
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(react_bootstrap__WEBPACK_IMPORTED_MODULE_3__["default"].Body, {
    className: "pt-0"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Bracket, {
    numRounds: numRounds,
    numWildcards: numWildcards,
    wildcardPlacement: wildcardPlacement
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