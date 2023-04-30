import React, { useState, useEffect } from 'react';
import { Container, Button, Table, Modal } from 'react-bootstrap';
import { BracketModal } from '../bracket_builder/Bracket';
import { BracketResponse, bracketApi } from '../../api/bracketApi';

// class BracketResponse {
// 	id: number;
// 	name: string;
// 	active: boolean;

// 	constructor(id: number, name: string, active: boolean) {
// 		this.id = id;
// 		this.name = name;
// 		this.active = active;
// 	}
// }

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
	bracket: BracketResponse;
	handleViewBracket: (bracketId: number) => void;
	handleDeleteBracket: (bracketId: number) => void;
}

const BracketRow: React.FC<BracketRowProps> = (props) => {
	const [showDeleteModal, setShowDeleteModal] = useState(false);
	const [active, setActive] = useState<boolean>(props.bracket.active);
	const bracket: BracketResponse = props.bracket;
	const handleViewBracket = props.handleViewBracket;
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

	return (
		<>
			<tr onClick={() => handleViewBracket(bracket.id)}>
				<td>{bracket.name}</td>
				<td className='text-center'>
					<input
						type="checkbox"
						checked={active}
						onClick={handleActiveToggle}
					/>
				</td>
				<td className='wpbb-bracket-table-action-col'>
					<Button variant="primary" >Score</Button>
					<Button variant="success" className='mx-2'>Copy</Button>
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
	brackets: BracketResponse[];
	handleShowBracketModal: (bracketId: number | null) => void;
	handleDeleteBracket: (bracketId: number) => void;
}

const BracketTable: React.FC<BracketTableProps> = (props) => {
	const brackets: BracketResponse[] = props.brackets;
	const handleShowBracketModal = props.handleShowBracketModal;

	const handleViewBracket = (bracketId: number) => {
		handleShowBracketModal(bracketId);
	}


	return (
		<Table hover className='table-dark wpbb-bracket-table'>
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col" className='text-center'>Active</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				{brackets.map((bracket) => (
					<BracketRow
						key={bracket.id}
						bracket={bracket}
						handleViewBracket={handleViewBracket}
						handleDeleteBracket={props.handleDeleteBracket}
					/>
				))}
			</tbody>
		</Table>
	);
};



const Settings = () => {
	const [showBracketModal, setShowBracketModal] = useState(false)
	const [brackets, setBrackets] = useState<BracketResponse[]>([]);
	const [activeBracketId, setActiveBracketId] = useState<number | null>(null);

	const handleCloseBracketModal = () => {
		console.log('close')
		setActiveBracketId(null);
		setShowBracketModal(false);
	}
	const handleSaveBracketModal = () => setShowBracketModal(false);
	const handleShowBracketModal = (bracketId: number | null = null) => {
		setActiveBracketId(bracketId);
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
			<Button variant='dark' className='mt-6' onClick={() => handleShowBracketModal()}>Add Bracket</Button>
			<BracketModal show={showBracketModal} bracketId={activeBracketId} handleClose={handleCloseBracketModal} handleSave={handleSaveBracketModal} />
		</Container>
	);
}




export default Settings; 