import React, { useState, useEffect } from 'react';
import { Modal, Table, Button } from 'react-bootstrap';
import { bracketApi } from '../../../api/bracketApi';
import { SubmissionRes } from '../../../api/types/bracket';
import { Nullable } from '../../../utils/types';
import { Bracket } from '../../../bracket/components/Bracket';
import { MatchTree } from '../../../bracket/models/MatchTree';
// import { BracketRes } from '../../api/types/bracket';

interface SubmissionRowProps {
	submission: SubmissionRes;
	handleViewSub: (subId: Nullable<number>) => void;
}

const SubmissionRow = (props: SubmissionRowProps) => {
	const {
		submission,
		handleViewSub
	} = props;
	return (
		<tr onClick={() => handleViewSub(submission.id)}>
			<td>{submission.id}</td>
			<td>{submission.name}</td>
			<td>12</td>
		</tr>
	);
};

interface SubmissionsTableProps {
	submissions: SubmissionRes[];
	handleViewSub: (subId: Nullable<number>) => void;
}

const SubmissionsTable = (props: SubmissionsTableProps) => {
	const {
		submissions,
		handleViewSub
	} = props;
	return (
		<Table bordered hover>
			<thead>
				<tr>
					<th>Customer</th>
					<th>Name</th>
					<th>Score</th>
				</tr>
			</thead>
			<tbody>
				{submissions.map((submission) => (
					<SubmissionRow key={submission.id} submission={submission} handleViewSub={handleViewSub} />
				))}
			</tbody>
		</Table>
	);
};

interface SubmissionModalBodyProps {
	bracketId: number;
	submissions: SubmissionRes[];
}

const SubmissionModalBody = (props: SubmissionModalBodyProps) => {
	const {
		bracketId,
		submissions,
	} = props;
	const [activeSub, setActiveSub] = useState<Nullable<SubmissionRes>>(null);

	const handleViewSub = (subId: Nullable<number>) => {
		if (subId === null) {
			setActiveSub(null);
			return;
		}
		bracketApi.getSubmission(subId).then((sub) => {
			setActiveSub(sub);
		});
	};

	if (submissions === null) {
		return <div>Loading...</div>
	}
	if (submissions.length === 0) {
		return <div>No submissions yet.</div>
	}
	if (activeSub !== null) {
		const matchTree = MatchTree.fromRounds(activeSub.rounds);
		return (
			<Bracket matchTree={matchTree} />
		);
	}
	return (
		<SubmissionsTable submissions={submissions} handleViewSub={handleViewSub} />
	);
}

interface BracketSubmissionsModalProps {
	show: boolean;
	handleClose: () => void;
	bracketId: number;
}

export const BracketSubmissionsModal = (props: BracketSubmissionsModalProps) => {
	const {
		show,
		handleClose,
		bracketId
	} = props;
	const [submissions, setSubmissions] = useState<Nullable<SubmissionRes[]>>(null);
	const [showBackButton, setShowBackButton] = useState<boolean>(true);

	useEffect(() => {
		bracketApi.getSubmissions(bracketId).then((submissions) => {
			setSubmissions(submissions);
		})
	}, [bracketId])

	const handleBack = (callback?: () => void) => {
		if (callback) callback();
		setShowBackButton(false);
	}

	return (
		<Modal className='wpbb-bracket-modal' show={show && submissions !== null} onHide={handleClose} size='xl' centered={true}>
			<Modal.Header className='wpbb-bracket-modal__header' closeButton>
				<Modal.Title>Submissions</Modal.Title>
			</Modal.Header >
			<Modal.Body className='pt-0'>
				{/* <BracketSubmissions bracketId={bracketId} /> */}
				{/* {submissions ? <SubmissionsTable submissions={submissions} /> : 'Loading...'} */}
				<SubmissionModalBody bracketId={bracketId} submissions={submissions || []} />
			</Modal.Body>
			<Modal.Footer className='wpbb-bracket-modal__footer'>
				{
					showBackButton &&
					<Button variant="primary" onClick={() => handleBack()}>
						Back
					</Button>
				}
				<Button variant="secondary" className='wpbb-bracket-modal-close-btn' onClick={handleClose}>
					Close
				</Button>
			</Modal.Footer>
		</Modal>
	)

}