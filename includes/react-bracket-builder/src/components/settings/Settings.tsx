import React, { useState, useEffect } from 'react';
import { Container, Button, Table } from 'react-bootstrap';
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


interface BracketRowProps {
	bracket: BracketResponse;
	handleViewBracket: (bracketId: number) => void;
	handleDeleteBracket: (bracketId: number) => void;
}

const BracketRow: React.FC<BracketRowProps> = (props) => {
	const bracket: BracketResponse = props.bracket;
	const handleViewBracket = props.handleViewBracket;
	const handleDeleteBracket = (e) => {
		e.stopPropagation();
		props.handleDeleteBracket(bracket.id);
	}


	return (
		<tr onClick={() => handleViewBracket(bracket.id)}>
			<td>{bracket.name}</td>
			{/* <td>{bracket.active ? <span className='wpbb-bracket-table-active-check'>&#10003;</span> : ''}</td> */}
			<td className='text-center'>
				<input
					type="checkbox"
					checked={bracket.active}
				/>
			</td>
			<td className='wpbb-bracket-table-action-col'>
				{/* <Button variant="light" >{bracket.active ? 'deactivate' : 'activate'}</Button> */}
				<Button variant="primary" >Score</Button>
				<Button variant="success" className='mx-2'>Copy</Button>
				<Button variant="danger" onClick={handleDeleteBracket}>Delete</Button>
			</td>
		</tr>
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

const BracketListItem = (props) => {
	const bracket: BracketResponse = props.bracket;
	return (
		<li>{bracket.name}</li>
	)
}


const BracketList = (props) => {
	const brackets: BracketResponse[] = props.brackets;
	return (
		<ul>
			{brackets.map((bracket) => <BracketListItem key={bracket.id} bracket={bracket} />)}
		</ul>
	)
}


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