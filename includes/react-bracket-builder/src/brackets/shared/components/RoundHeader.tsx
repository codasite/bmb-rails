import React, { useState, useEffect } from 'react';
import { Form } from 'react-bootstrap';
import { Round } from '../models/MatchTree';
//@ts-ignore

interface RoundHeaderProps {
	round: Round;
	updateRoundName?: (roundId: number, name: string) => void;
}

export const RoundHeader = (props: RoundHeaderProps) => {
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
