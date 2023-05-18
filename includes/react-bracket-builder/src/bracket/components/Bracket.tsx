import React, { useState, useEffect, useRef } from 'react';
import { Button } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { Nullable } from '../../utils/types';
import { MatchTree, Round, MatchNode, Team, WildcardPlacement } from '../models/MatchTree';
// import html2canvas
import html2canvas from 'html2canvas';


// Direction enum
enum Direction {
	TopLeft = 0,
	TopRight = 1,
	Center = 2,
	BottomLeft = 3,
	BottomRight = 4,
}


interface TeamSlotProps {
	className: string;
	team?: Team | null;
	updateTeam?: (name: string) => void;
	pickTeam?: () => void;
}

const TeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')

	const {
		team,
		updateTeam,
		pickTeam
	} = props


	const startEditing = () => {
		if (!updateTeam) {
			return
		}
		setEditing(true)
		setTextBuffer(team ? team.name : '')
	}

	const doneEditing = (e) => {
		if (!updateTeam) {
			return
		}
		if (!team && textBuffer !== '' || team && textBuffer !== team.name) {
			updateTeam(textBuffer)
		}
		setEditing(false)
	}

	const handleClick = (e) => {
		if (updateTeam) {
			startEditing()
		} else if (pickTeam) {
			pickTeam()
		}
	}

	return (
		<div className={props.className} onClick={handleClick}>
			{editing ?
				<input
					className='wpbb-team-name-input'
					autoFocus
					onFocus={(e) => e.target.select()}
					type='text'
					value={textBuffer}
					onChange={(e) => setTextBuffer(e.target.value)}
					onBlur={doneEditing}
					onKeyUp={(e) => {
						if (e.key === 'Enter') {
							doneEditing(e)
						}
					}}
				/>
				:
				<span className='wpbb-team-name'>{team ? team.name : ''}</span>
			}
		</div>
	)
}

interface MatchBoxProps {
	match: MatchNode | null;
	direction: Direction;
	height: number;
	spacing: number;
	updateTeam?: (left: boolean, name: string) => void;
	pickTeam?: (left: boolean) => void;
}

const MatchBox = (props: MatchBoxProps) => {
	const {
		match,
		direction,
		height,
		spacing,
		updateTeam,
		pickTeam,
	} = props

	if (match === null) {
		return (
			<div className='wpbb-match-box-empty' style={{ height: height + spacing }} />
		)
	}

	let className: string;

	if (direction === Direction.TopLeft || direction === Direction.BottomLeft) {
		// Left side of the bracket
		className = 'wpbb-match-box-left'
	} else if (direction === Direction.TopRight || direction === Direction.BottomRight) {
		// Right side of the bracket
		className = 'wpbb-match-box-right'
	} else {
		className = 'wpbb-match-box-center'
	}

	const upperOuter = match.left === null
	const lowerOuter = match.right === null

	if (upperOuter && lowerOuter) {
		// First round
		className += '-outer'
	} else if (upperOuter) {
		// Upper bracket
		className += '-outer-upper'
	} else if (lowerOuter) {
		// Lower bracket
		className += '-outer-lower'
	}

	// This component renders the lines connecting two nodes representing a "game"
	// These should be evenly spaced in the column and grow according to the number of other matches in the round
	return (
		<div className={className} style={{ height: height, marginBottom: spacing }}>
			<TeamSlot
				className='wpbb-team1'
				team={match.team1}
				updateTeam={updateTeam ? (name: string) => updateTeam(true, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(true) : undefined}
			/>
			{direction === Direction.Center && <TeamSlot className='wpbb-champion-team' team={match.result} />}
			<TeamSlot
				className='wpbb-team2'
				team={match.team2}
				updateTeam={updateTeam ? (name: string) => updateTeam(false, name) : undefined}
				pickTeam={pickTeam ? () => pickTeam(false) : undefined}
			/>
		</div>
	)
}

interface RoundHeaderProps {
	round: Round;
	updateRoundName?: (roundId: number, name: string) => void;
}

const RoundHeader = (props: RoundHeaderProps) => {
	const [editRoundName, setEditRoundName] = useState(false);
	const [nameBuffer, setNameBuffer] = useState('');
	const {
		round,
		updateRoundName,
	} = props

	const canEdit = updateRoundName !== undefined

	useEffect(() => {
		setNameBuffer(props.round.name)
	}, [props.round.name])

	const startEditing = () => {
		if (!canEdit) {
			return
		}
		setEditRoundName(true)
		setNameBuffer(round.name)
	}

	const doneEditing = () => {
		if (!canEdit) {
			return
		}
		setEditRoundName(false)
		updateRoundName(props.round.id, nameBuffer)
	}

	return (
		<div className='wpbb-round__header'>
			{editRoundName ? <Form.Control type='text'
				value={nameBuffer}
				autoFocus
				onFocus={(e) => e.target.select()}
				onBlur={() => doneEditing()}
				onChange={(e) => setNameBuffer(e.target.value)}
				onKeyUp={(e) => {
					if (e.key === 'Enter') {
						doneEditing()
					}
				}}
			/>
				:
				<span onClick={startEditing}>{round.name}</span>
			}
		</div>
	)
}

interface MatchColumnProps {
	round: Round;
	matches: Nullable<MatchNode>[];
	direction: Direction;
	numDirections: number;
	matchHeight: number;
	updateRoundName?: (roundId: number, name: string) => void;
	updateTeam?: (roundId: number, matchIndex: number, left: boolean, name: string) => void;
	pickTeam?: (matchIndex: number, left: boolean) => void;
}

const MatchColumn = (props: MatchColumnProps) => {
	const {
		round,
		matches,
		direction,
		numDirections,
		matchHeight,
		updateRoundName,
		updateTeam,
		pickTeam,
	} = props
	// const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
	const canEdit = updateTeam !== undefined && updateRoundName !== undefined

	const buildMatches = () => {
		const matchBoxes = matches.map((match, i) => {
			const matchIndex = direction === Direction.TopLeft || direction === Direction.BottomLeft ? i : i + matches.length
			return (
				<MatchBox
					match={match}
					direction={direction}
					height={matchHeight}
					spacing={i + 1 < matches.length ? matchHeight : 0} // Do not add spacing to the last match in the round column
					updateTeam={canEdit ? (left: boolean, name: string) => updateTeam(round.id, matchIndex, left, name) : undefined}
					pickTeam={pickTeam ? (left: boolean) => pickTeam(matchIndex, left) : undefined}
				/>
			)
		})
		return matchBoxes

	}
	return (
		<div className='wpbb-round'>
			<RoundHeader round={round} updateRoundName={canEdit ? updateRoundName : undefined} />
			<div className='wpbb-round__body'>
				{buildMatches()}
			</div>
		</div>
	)
}


interface BracketProps {
	matchTree: MatchTree;
	canEdit?: boolean;
	canPick?: boolean;
	setMatchTree?: (matchTree: MatchTree) => void;
}

export const Bracket = (props: BracketProps) => {
	// const { numRounds, numWildcards, wildcardPlacement } = props
	// const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(numRounds, numWildcards, wildcardPlacement))
	const {
		matchTree,
		setMatchTree,
	} = props

	const rounds = matchTree.rounds
	const canEdit = setMatchTree !== undefined && props.canEdit
	const canPick = setMatchTree !== undefined && props.canPick


	const updateRoundName = (roundId: number, name: string) => {
		if (!canEdit) {
			return
		}
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			roundToUpdate.name = name;
			setMatchTree(newMatchTree);
		}
	};

	const updateTeam = (roundId: number, matchIndex: number, left: boolean, name: string) => {
		if (!canEdit) {
			return
		}
		const newMatchTree = matchTree.clone();
		const roundToUpdate = newMatchTree.rounds.find((round) => round.id === roundId);
		if (roundToUpdate) {
			const matchToUpdate = roundToUpdate.matches[matchIndex];
			if (matchToUpdate) {
				if (left) {
					const team = matchToUpdate.team1;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team1 = new Team(name);
					}
				} else {
					const team = matchToUpdate.team2;
					if (team) {
						team.name = name;
					} else {
						matchToUpdate.team2 = new Team(name);
					}
				}
			}
			setMatchTree(newMatchTree);
		}
	}

	const pickTeam = (depth: number, matchIndex: number, left: boolean) => {
		if (!canPick) {
			return
		}
		const newMatchTree = matchTree.clone()
		newMatchTree.advanceTeam(depth, matchIndex, left)
		setMatchTree(newMatchTree)
	}

	const targetHeight = 800;

	// The number of rounds sets the initial height of each match
	const firstRoundMatchHeight = targetHeight / 2 ** (rounds.length - 2) / 2;

	const bracketRef = useRef<HTMLDivElement>(null)

	/**
	 * Build rounds in two directions, left to right and right to left
	 */
	const buildRounds2 = (rounds: Round[]) => {
		// Assume rounds are sorted by depth
		// Rendering from left to right, sort by depth descending
		const numDirections = 2

		return [
			...rounds.slice(1).reverse().map((round, idx) => {
				// Get the first half of matches for this column
				const colMatches = round.matches.slice(0, round.matches.length / 2)

				return <MatchColumn
					matches={colMatches}
					round={round} direction={Direction.TopLeft}
					numDirections={numDirections}
					matchHeight={2 ** idx * firstRoundMatchHeight}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			}),
			// handle final round differently
			<MatchColumn
				matches={rounds[0].matches}
				round={rounds[0]}
				direction={Direction.Center}
				numDirections={numDirections}
				matchHeight={targetHeight / 4}
				updateRoundName={canEdit ? updateRoundName : undefined}
				updateTeam={canEdit ? updateTeam : undefined}
				pickTeam={canPick ?
					(_, left: boolean) => pickTeam(0, 0, left)
					: undefined}
			/>,
			...rounds.slice(1).map((round, idx, arr) => {
				// Get the second half of matches for this column
				const colMatches = round.matches.slice(round.matches.length / 2)

				return <MatchColumn round={round}
					matches={colMatches}
					direction={Direction.TopRight}
					numDirections={numDirections}
					matchHeight={2 ** (arr.length - 1 - idx) * firstRoundMatchHeight}
					updateRoundName={canEdit ? updateRoundName : undefined}
					updateTeam={canEdit ? updateTeam : undefined}
					pickTeam={canPick ?
						(matchIndex: number, left: boolean) => pickTeam(round.depth, matchIndex, left)
						: undefined}
				/>
			})
		]
	}

	const screenshot = () => {
		const bracketEl: HTMLDivElement | null = bracketRef.current
		if (!bracketEl) {
			return
		}
		// const bracketHTML = bracketEl.outerHTML
		// console.log(bracketHTML)
		const userBracket = matchTree.toUserRequest('barry bracket', 999);
		const json = JSON.stringify(userBracket);
		console.log(json)

	}


	return (
		<>
			<div className='wpbb-bracket' ref={bracketRef}>
				{rounds.length > 0 && buildRounds2(rounds)}
			</div>
			<Button variant='primary' onClick={screenshot}>ref</Button>
		</>
	)
}


function getElementChildrenAndStyles(element: HTMLElement): string {
	const html = element.outerHTML;

	const elements = Array.from(element.querySelectorAll('*'));

	const rulesUsed: CSSStyleRule[] = [];

	const sheets = document.styleSheets;
	for (const sheet of Array.from(sheets)) {
		let cssRules: CSSRuleList | null = null;
		try {
			cssRules = sheet.cssRules;
		} catch (error) {
			console.warn('Failed to access cssRules for stylesheet:', sheet, error);
			continue;
		}

		if (!cssRules) {
			continue;
		}

		for (const rule of Array.from(cssRules)) {
			// Type guard to narrow down the type of the rule to CSSStyleRule
			if (rule instanceof CSSStyleRule) {
				const selectorText = rule.selectorText;
				const matchedElts = Array.from(document.querySelectorAll(selectorText));
				for (const elt of elements) {
					if (matchedElts.includes(elt)) {
						rulesUsed.push(rule);
						break;
					}
				}
			}
		}
	}

	const style = rulesUsed
		.map((cssRule) => {
			return `${cssRule.selectorText} { ${cssRule.style.cssText.toLowerCase()} }`;
		})
		.join('\n')
		.replace(/(\{|;)\s+/g, '$1\n  ')
		.replace(/\A\s+}/, '}');

	return `<style>\n${style}\n</style>\n\n${html}`;
}
