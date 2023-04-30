import React, { useState, useEffect } from 'react';
import { Container, Button, Table, Modal } from 'react-bootstrap';
import { BracketModal, BracketModalMode } from '../bracket_builder/Bracket';
import { BracketRes, bracketApi } from '../../api/bracketApi';

interface DeleteModalProps {
	show: boolean;
	onHide: () => void;
	onDelete: () => void;
}

const DeleteModal: React.FC<DeleteModalProps> = ({ show, onHide, onDelete }) => {
	return (
		<Modal show={show} onHide={onHide} centered={true}>
			<Modal.Header closeButton>
				<Modal.Title>Delete Bracket</Modal.Title>
			</Modal.Header>
			<Modal.Body>
				<p>
					Are you sure you want to delete this bracket? This will delete all associated user brackets and cannot be undone.
				</p>
			</Modal.Body>
			<Modal.Footer>
				<Button variant="secondary" onClick={onHide}>
					Cancel
				</Button>
				<Button variant="danger" onClick={onDelete}>
					Delete
				</Button>
			</Modal.Footer>
		</Modal>
	);
};


interface BracketRowProps {
	bracket: BracketRes;
	// handleViewBracket: (bracketId: number) => void;
	handleShowBracketModal: (mode: BracketModalMode, bracketId: number | null) => void;
	handleDeleteBracket: (bracketId: number) => void;
}

const BracketRow: React.FC<BracketRowProps> = (props) => {
	const [showDeleteModal, setShowDeleteModal] = useState(false);
	const [active, setActive] = useState<boolean>(props.bracket.active);
	const bracket: BracketRes = props.bracket;

	const handleViewBracket = () => {
		props.handleShowBracketModal(BracketModalMode.View, bracket.id);
	}

	const handleCopyBracket = (e) => {
		e.stopPropagation();
		props.handleShowBracketModal(BracketModalMode.New, bracket.id)
	}

	const handleShowDeleteDialog = (e) => {
		e.stopPropagation();
		setShowDeleteModal(true);
	}

	const handleActiveToggle = (e) => {
		e.stopPropagation();
		console.log('toggle')
		bracketApi.setActive(bracket.id, e.target.checked).then((isActive) => {
			setActive(isActive);
		})
		console.log(e.target.checked)
	}
	const created = new Date(bracket.createdAt.date);

	const timeAgo = (date: Date) => {
		const now = new Date();

		const diff = now.getTime() - date.getTime();
		const days = Math.floor(diff / (1000 * 60 * 60 * 24));
		if (days > 0) {
			return `${days} day${days > 1 ? 's' : ''} ago`;
		}
		const hours = Math.floor(diff / (1000 * 60 * 60));
		if (hours > 0) {
			return `${hours} hour${hours > 1 ? 's' : ''} ago`;
		}
		const minutes = Math.floor(diff / (1000 * 60));
		if (minutes > 0) {
			return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
		}
		const seconds = Math.floor(diff / (1000));
		if (seconds > 0) {
			return `${seconds} second${seconds > 1 ? 's' : ''} ago`;
		}
		return 'just now';
	};

	const creationTime = timeAgo(created);

	return (
		<>
			<tr onClick={handleViewBracket}>
				<td>{bracket.name}</td>
				<td className='text-center'>
					<input
						type="checkbox"
						checked={active}
						onClick={handleActiveToggle}
					/>
				</td>
				{/* <td className='text-center'>{bracket.userBracketCount}</td> */}
				<td className='text-center'>{creationTime}</td>
				<td className='wpbb-bracket-table-action-col'>
					<Button variant="primary" >Score</Button>
					<Button variant="success" className='mx-2' onClick={handleCopyBracket}>Copy</Button>
					<Button variant="danger" onClick={handleShowDeleteDialog}>Delete</Button>
				</td>
			</tr>
			<DeleteModal
				show={showDeleteModal}
				onHide={() => setShowDeleteModal(false)}
				onDelete={() => props.handleDeleteBracket(bracket.id)}
			/>
		</>
	);
};


interface BracketTableProps {
	brackets: BracketRes[];
	handleShowBracketModal: (mode: BracketModalMode, bracketId: number | null) => void;
	handleDeleteBracket: (bracketId: number) => void;
}

const BracketTable: React.FC<BracketTableProps> = (props) => {
	const brackets: BracketRes[] = props.brackets;

	return (
		<Table hover className='table-dark wpbb-bracket-table'>
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col" className='text-center'>Published</th>
					<th scope="col" className='text-center'>Created</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				{brackets.map((bracket) => (
					<BracketRow
						key={bracket.id}
						bracket={bracket}
						// handleViewBracket={handleViewBracket}
						// handleDeleteBracket={props.handleDeleteBracket}
						{...props}
					/>
				))}
			</tbody>
		</Table>
	);
};



const Settings = () => {
	const [showBracketModal, setShowBracketModal] = useState(false)
	const [brackets, setBrackets] = useState<BracketRes[]>([]);
	const [bracketModalMode, setBracketModalMode] = useState<BracketModalMode>(BracketModalMode.View);
	const [activeBracketId, setActiveBracketId] = useState<number | null>(null);

	const handleCloseBracketModal = () => {
		setActiveBracketId(null);
		setShowBracketModal(false);
	}
	const handleSaveBracketModal = (newBracket: BracketRes) => {
		setBrackets([...brackets, newBracket])
		handleCloseBracketModal();
	}
	const handleShowBracketModal = (mode: BracketModalMode, bracketId: number | null = null) => {
		setActiveBracketId(bracketId);
		setBracketModalMode(mode);
		setShowBracketModal(true);
	};
	const handleDeleteBracket = (bracketId: number) => {
		bracketApi.deleteBracket(bracketId).then(() => {
			setBrackets(brackets.filter((bracket) => bracket.id !== bracketId));
		})
	}
	// const handleCopyBracket = (bracketId: number) => {
	// 	bracketApi.(bracketId).then((bracket) => {

	useEffect(() => {
		bracketApi.getBrackets().then((brackets) => {
			console.log(brackets);
			setBrackets(brackets);
		});
	}, []);

	return (
		<Container >
			<h2 className='mt-4 mb-4'>Bracket Builder Settings</h2>
			{/* <BracketList brackets={brackets} /> */}
			<BracketTable
				brackets={brackets}
				handleShowBracketModal={handleShowBracketModal}
				handleDeleteBracket={handleDeleteBracket}
			/>
			<Button variant='dark' className='mt-6' onClick={() => handleShowBracketModal(BracketModalMode.New)}>New Bracket</Button>
			<BracketModal
				show={showBracketModal}
				mode={bracketModalMode}
				bracketId={activeBracketId}
				handleClose={handleCloseBracketModal}
				handleSave={handleSaveBracketModal}
			/>
		</Container>
	);
}

export default Settings; 