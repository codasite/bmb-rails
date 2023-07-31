import React, { useState } from 'react';
import { Team } from '../models/MatchTree';
//@ts-ignore
import { getTeamClasses } from '../utils';

interface TeamSlotProps {
	className?: string;
	team?: Team | null;
	updateTeam?: (name: string) => void;
	pickTeam?: () => void;
	roundIndex?: number;
	matchIndex?: number;
	left?: boolean;
	winner?: boolean;
}

export const TeamSlot = (props: TeamSlotProps) => {
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState('')

	const {
		team,
		updateTeam,
		pickTeam,
		roundIndex,
		matchIndex,
		left,
		winner,
	} = props
	console.log('winner', winner)

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
	console.log('className', className)

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
		<div className={className} onClick={handleClick}>
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
				// <span className='wpbb-team-name'>{roundIndex}-{matchIndex}-{left ? 'left' : 'right'}</span>
				// <span className='wpbb-team-name'>Team</span>
			}
		</div>
	)
}