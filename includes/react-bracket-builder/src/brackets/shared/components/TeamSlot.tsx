import React, { useState, useContext } from 'react';
import { MatchNode, Round, Team } from '../models/MatchTree';
//@ts-ignore
import { getTeamClasses } from '../utils';
import { bracketConstants } from '../constants';
import { BracketContext } from '../context';

interface TeamSlotProps {
	className?: string;
	team?: Team | null;
	updateTeam?: (name: string) => void;
	pickTeam?: () => void;
	roundIndex?: number;
	matchIndex?: number;
	left?: boolean;
	winner?: boolean;
	match?: MatchNode | null;
	round?: Round;
}

export const TeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')
	const bracket = useContext(BracketContext);

	const {
		team,
		updateTeam,
		pickTeam,
		roundIndex,
		matchIndex,
		left,
		winner,
		match,
		round
	} = props
	// console.log('winner', winner)

	let className = props.className
	// const className = props.className ? props.className : 'wpbb-team ' + getTeamClasses(roundIndex, matchIndex, left) + (winner ? ' wpbb-match-winner' : '')
	if (className === undefined) {
		className = 'wpbb-team'
		if (roundIndex !== undefined && matchIndex !== undefined && left !== undefined) {
			className += ' ' + getTeamClasses(roundIndex, matchIndex, left)
		}
		if (winner) {
			className += ' wpbb-match-winner'
		}
	}
	// console.log('className', className)

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
	const isReadOnly = (round, match,left) => {
		//in user bracket window all the fields will be read only
		if(!bracket?.numRounds){
			return
		}
		if (round?.depth === (bracket?.numRounds - 1)) {
			return false;
		}
		else if (round?.depth === (bracket?.numRounds - 2)) {
			if (match.left === null && left === true) {
				return false;
			}
			else if (match.right === null && left !== true) {
				return false;
			}
			else {
				return true;
			}
		}
		return true;
	}
	const setBackground = (left) =>{
		//backgroud color will get changed base on user selection on number of teams
		let backgroundColor = isReadOnly(round, match, left)
		if(bracket?.canEdit){
			if(!backgroundColor){
				return bracketConstants.color3	
			}
		}
	}

	return (
		<div className={className} onClick={handleClick} style={{ background :setBackground(left)}}>
			{editing && !isReadOnly(round, match,left) ?
				<input
					className='wpbb-team-name-input'
					style={{background: 'none', border: 'none', color:'#FFFFFF'}}
					autoFocus
					onFocus={(e) => e.target.select()}
					type='text'
					// readOnly={isReadOnly(props.round, props.match, props.className)}
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
				<span className='wpbb-team-name'>{team ? team.name : (isReadOnly(round, match,left) ? '':textBuffer? textBuffer :'ADD TEAM')}</span>
				// <span className='wpbb-team-name'>{roundIndex}-{matchIndex}-{left ? 'left' : 'right'}</span>
				// <span className='wpbb-team-name'>Team</span>
			}
		</div>
	)
}