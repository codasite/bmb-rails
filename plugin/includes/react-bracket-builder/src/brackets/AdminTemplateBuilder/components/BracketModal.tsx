import React, { useState, useEffect } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../shared/api/bracketApi';
import { Nullable } from '../../../utils/types';
import { Bracket } from '../../shared/components/Bracket/Bracket';
import { MatchTree, WildcardPlacement } from '../../shared/models/MatchTree';
import { BracketRes } from '../../shared/api/types/bracket';
import { BracketSubmissionsModal } from './BracketSubmissionsModal';


export enum BracketModalMode {
	New = 0,
	View = 1,
	Score = 2,
	Submissions = 3
}

const NumRoundsSelector = (props) => {
	const {
		numRounds,
		setNumRounds
	} = props

	const minRounds = 1;
	const maxRounds = 6;

	const options = Array.from(Array(maxRounds - minRounds + 1).keys()).map((i) => {
		return (
			<option value={i + minRounds}>{i + minRounds}</option>
		)
	})

	const handleChange = (event) => {
		const num = event.target.value
		setNumRounds(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Number of Rounds:
			</label>
			<select value={numRounds} onChange={handleChange}>
				{options}
			</select>
		</div>
	)
}

const NumWildcardsSelector = (props) => {
	const {
		numWildcards,
		setNumWildcards,
		maxWildcards,
	} = props

	const minWildcards = 0;

	// Number of wildcards must be an even number or 0
	let options = [
		<option value={0}>0</option>
	]
	options = [...options, ...Array.from(Array(maxWildcards / 2).keys()).reverse().map((i) => {
		return (
			<option value={(i + 1) * 2}>{(i + 1) * 2}</option>
		)
	})]

	const handleChange = (event) => {
		const num = event.target.value
		setNumWildcards(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Wildcard Games:
			</label>
			<select value={numWildcards} onChange={handleChange}>
				{options}
			</select>
		</div>
	)
}

interface WildcardPlacementSelectorProps {
	wildcardPlacement: WildcardPlacement;
	setWildcardPlacement: (wildcardPlacement: WildcardPlacement) => void;
	disabled: boolean;
}

const WildcardPlacementSelector = (props: WildcardPlacementSelectorProps) => {
	const {
		wildcardPlacement,
		setWildcardPlacement,
		disabled,
	} = props

	const options = [
		<option value={WildcardPlacement.Bottom}>Bottom</option>,
		<option value={WildcardPlacement.Top}>Top</option>,
		<option value={WildcardPlacement.Split}>Split</option>,
		<option value={WildcardPlacement.Center}>Centered</option>,
	]

	const handleChange = (event) => {
		const num = event.target.value
		setWildcardPlacement(parseInt(num))
	}

	return (
		<div className='wpbb-option-group'>
			<label>
				Wildcard Placement:
			</label>
			<select value={wildcardPlacement} onChange={handleChange} disabled={disabled}>
				{options}
			</select>
		</div>
	)
}

const BracketTitle = (props) => {
	const {
		title,
		setTitle,
	} = props
	const [editing, setEditing] = useState(false)
	const [textBuffer, setTextBuffer] = useState(title)

	const startEditing = () => {
		setEditing(true)
		setTextBuffer(title)
	}

	const doneEditing = (event) => {
		setTitle(textBuffer)
		setEditing(false)
	}

	return (
		<div className='wpbb-bracket-title' onClick={startEditing}>
			{editing ?
				<input
					className='wpbb-bracket-title-input'
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
				<span className='wpbb-bracket-title-name'>{title}</span>
			}
		</div>
	)
}

interface ViewBracketModalProps {
	show: boolean;
	handleClose: () => void;
	bracketId: number;
}

const ViewBracketModal = (props: ViewBracketModalProps) => {
	const {
		show,
		handleClose,
		bracketId
	} = props;
	const [matchTree, setMatchTree] = useState<MatchTree | null>(null)
	const [bracket, setBracket] = useState<BracketRes | null>(null)

	useEffect(() => {
		bracketApi.getBracket(bracketId)
			.then((bracket) => {
				setBracket(bracket)
				setMatchTree(MatchTree.fromRounds(bracket.rounds))
			})
	}, [bracketId])

	return (
		<Modal className='wpbb-bracket-modal' show={show && bracket !== null} onHide={handleClose} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title>{bracket?.name}</Modal.Title>
			</Modal.Header >
			<Modal.Body className='pt-0 wpbb-default'>
				{matchTree ? <Bracket matchTree={matchTree} setMatchTree={setMatchTree} canPick /> : 'Loading...'}
			</Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				<Button variant="secondary" onClick={handleClose}>
					Close
				</Button>
			</Modal.Footer>
		</Modal>
	)
}

interface NewBracketModalProps {
	show: boolean;
	handleClose: () => void;
	handleSave: (bracket: BracketRes) => void;
	bracketId: Nullable<number>;
}

const NewBracketModal = (props: NewBracketModalProps) => {
	const defaultNumRounds = 4;
	const defaultNumWildcards = 0;
	const defaultWildcardPlacement = WildcardPlacement.Bottom;
	const defaultBracketName = 'New Bracket';
	const {
		show,
		handleClose,
		handleSave,
		bracketId
	} = props;
	const [numRounds, setNumRounds] = useState(defaultNumRounds);
	const [numWildcards, setNumWildcards] = useState(defaultNumWildcards);
	const [wildcardPlacement, setWildcardPlacement] = useState(defaultWildcardPlacement);
	const [bracketName, setBracketName] = useState(defaultBracketName);
	const [matchTree, setMatchTree] = useState<MatchTree>(MatchTree.fromOptions(defaultNumRounds, defaultNumWildcards, defaultWildcardPlacement));
	// The max number of wildcards is 2 less than the possible number of matches in the first round
	const maxWildcards = 2 ** (numRounds - 1) - 2;

	useEffect(() => {
		if (bracketId) {
			bracketApi.getBracket(bracketId)
				.then((bracket) => {
					setNumRounds(bracket.numRounds)
					setNumWildcards(bracket.numWildcards)
					if (bracket.wildcardPlacement) {
						setWildcardPlacement(bracket.wildcardPlacement)
					}
					setBracketName(`${bracket.name} (Copy)`)
					setMatchTree(MatchTree.fromRounds(bracket.rounds))
				})
		}
		else {
			rebuildMatchTree(defaultNumRounds, defaultNumWildcards, defaultWildcardPlacement);
		}
	}, [bracketId])

	const updateNumRounds = (num: number) => {
		setNumRounds(num);
		rebuildMatchTree(num, numWildcards, wildcardPlacement);
	};

	const updateNumWildcards = (num: number) => {
		setNumWildcards(num);
		rebuildMatchTree(numRounds, num, wildcardPlacement);
	};

	const updateWildcardPlacement = (placement: WildcardPlacement) => {
		setWildcardPlacement(placement);
		rebuildMatchTree(numRounds, numWildcards, placement);
	};

	const rebuildMatchTree = (
		updatedNumRounds: number,
		updatedNumWildcards: number,
		updatedWildcardPlacement: WildcardPlacement
	) => {
		setMatchTree(MatchTree.fromOptions(updatedNumRounds, updatedNumWildcards, updatedWildcardPlacement));
	};

	const handleSaveBracket = () => {
		const req = matchTree.toRequest(bracketName, true, numRounds, numWildcards, wildcardPlacement)
		bracketApi.createBracket(req)
			.then((newBracket) => {
				handleSave(newBracket)
			})
	}

	return (
		<Modal className='wpbb-bracket-modal' show={show} onHide={handleClose} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title><BracketTitle title={bracketName} setTitle={setBracketName} /></Modal.Title>
				<form className='wpbb-options-form'>
					<NumRoundsSelector
						numRounds={numRounds}
						setNumRounds={updateNumRounds}
					/>
					<NumWildcardsSelector
						numWildcards={numWildcards}
						setNumWildcards={updateNumWildcards}
						maxWildcards={maxWildcards}
					/>
					<WildcardPlacementSelector
						wildcardPlacement={wildcardPlacement}
						setWildcardPlacement={updateWildcardPlacement}
						disabled={numWildcards > 0 ? false : true}
					/>
				</form>
			</Modal.Header >
			<Modal.Body className='pt-0 wpbb-default'><Bracket matchTree={matchTree} setMatchTree={setMatchTree} canEdit /></Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				<Button variant="secondary" onClick={handleClose}>
					Close
				</Button>
				<Button variant="primary" onClick={handleSaveBracket}>
					Save
				</Button>
			</Modal.Footer>
		</Modal>
	)
}


interface BracketModalProps {
	show: boolean;
	handleClose: () => void;
	handleSave: (bracket: BracketRes) => void;
	mode: BracketModalMode;
	bracketId: Nullable<number>;
}


export const BracketModal = (props: BracketModalProps) => {
	const bracketId = props.bracketId;

	if (bracketId !== null) {
		if (props.mode === BracketModalMode.New) {
			return <NewBracketModal {...props} />
		} else if (props.mode === BracketModalMode.Score) {
			return <ViewBracketModal {...props} bracketId={bracketId} />
		} else if (props.mode === BracketModalMode.Submissions) {
			return <BracketSubmissionsModal {...props} bracketId={bracketId} />
		} else {
			return <ViewBracketModal {...props} bracketId={bracketId} />
		}
	} else {
		return <NewBracketModal {...props} />
	}
}