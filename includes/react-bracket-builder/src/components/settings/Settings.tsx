import React, { useState, useEffect } from 'react';
import { Container, Button, Table } from 'react-bootstrap';
import { BracketModal, Bracket } from '../bracket_builder/Bracket';

// class BracketTemplate {
// 	id: number;
// 	name: string;
// 	active: boolean;

// 	constructor(id: number, name: string, active: boolean) {
// 		this.id = id;
// 		this.name = name;
// 		this.active = active;
// 	}
// }

interface BracketTemplate {
	id: number;
	name: string;
	active: boolean;
}

class BracketApi {
	private baseUrl: string;
	private bracketPath: string = 'brackets';

	constructor() {
		// @ts-ignore
		this.baseUrl = wpbb_ajax_obj.rest_url;
	}

	async getBrackets(): Promise<BracketTemplate[]> {
		return await this.performRequest(this.bracketPath, 'GET');
	}

	async performRequest(path: string, method: string, body: any = {}) {
		const request = {
			method,
			headers: {
				'Content-Type': 'application/json'
			},
		}
		if (method !== 'GET') {
			request['body'] = JSON.stringify(body);
		}

		const response = await fetch(`${this.baseUrl}${path}`, request);
		return response.json();
	}
}

const bracketApi = new BracketApi();

interface BracketRowProps {
	bracket: BracketTemplate;
}

const BracketRow: React.FC<BracketRowProps> = ({ bracket }) => {
	return (
		<tr>
			<td>{bracket.name}</td>
			{/* <td>{bracket.active ? <span className='wpbb-bracket-table-active-check'>&#10003;</span> : ''}</td> */}
			<td className='text-center'>
				<input
					type="checkbox"
					checked={bracket.active}
				// disabled
				// readOnly
				/>
			</td>
			<td className='wpbb-bracket-table-action-col'>
				{/* <Button variant="light" >{bracket.active ? 'deactivate' : 'activate'}</Button> */}
				<Button variant="primary" className='mx-2'>Score</Button>
				<Button variant="danger">Delete</Button>
			</td>
		</tr>
	);
};


interface BracketTableProps {
	brackets: BracketTemplate[];
}

const BracketTable: React.FC<BracketTableProps> = ({ brackets }) => {
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
					<BracketRow key={bracket.id} bracket={bracket} />
				))}
			</tbody>
		</Table>
	);
};

const BracketListItem = (props) => {
	const bracket: BracketTemplate = props.bracket;
	return (
		<li>{bracket.name}</li>
	)
}


const BracketList = (props) => {
	const brackets: BracketTemplate[] = props.brackets;
	return (
		<ul>
			{brackets.map((bracket) => <BracketListItem key={bracket.id} bracket={bracket} />)}
		</ul>
	)
}


const Settings = () => {
	const [showBracketModal, setShowBracketModal] = useState(false)
	const [brackets, setBrackets] = useState<BracketTemplate[]>([]);

	const handleCloseBracketModal = () => setShowBracketModal(false);
	const handleSaveBracketModal = () => setShowBracketModal(false);
	const handleShowBracketModal = () => setShowBracketModal(true);

	useEffect(() => {
		bracketApi.getBrackets().then((brackets) => {
			console.log(brackets);
			setBrackets(brackets);
		});
	}, []);

	return (
		<Container >
			<h3 className='mt-4'>Bracket Builder Settings</h3>
			{/* <BracketList brackets={brackets} /> */}
			<BracketTable brackets={brackets} />
			<Button variant='dark' className='mt-6' onClick={handleShowBracketModal}>Create Bracket</Button>
			<BracketModal show={showBracketModal} handleCancel={handleCloseBracketModal} handleSave={handleSaveBracketModal} />
		</Container>
	);
}




export default Settings; 