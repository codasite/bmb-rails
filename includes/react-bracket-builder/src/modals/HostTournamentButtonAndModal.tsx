import {ReactComponent as SignalIcon} from "../brackets/shared/assets/signal.svg";
import * as React from 'react';
import {useState} from 'react';
import {Modal} from './Modal';
import {bracketApi} from '../brackets/shared/api/bracketApi';

export const HostTournamentButtonAndModal = (props: {
  templateId: string,
  tournamentsUrl: string,
}) => {
  const [showModal, setShowModal] = useState(false);
  const [loading, setLoading] = useState(false);
  const [tournamentName, setTournamentName] = useState('');
  const [hasError, setHasError] = useState(false);
  const cancelButton =
    <button
      onClick={() => setShowModal(false)}
      className="tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-12 tw-border-none hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-cursor-pointer">
      Cancel
    </button>;
  return <>
    <button
      className="tw-border tw-border-solid tw-border-blue tw-bg-blue/15 tw-px-16 tw-py-12 tw-flex tw-gap-10 tw-items-center tw-justify-center tw-rounded-8 hover:tw-bg-white hover:tw-text-black tw-font-sans tw-text-white tw-uppercase tw-w-full tw-cursor-pointer"
      onClick={() => setShowModal(true)}>
      <SignalIcon/>
      <span className="tw-font-700">Host Tournament</span>
    </button>
    <Modal show={showModal} setShow={setShowModal}>
      <h1 className="tw-text-32 tw-leading-10 tw-font-white tw-whitespace-pre-line tw-mb-30">Host tournament</h1>
      <input
        className={"tw-border-0 tw-border-b tw-border-white tw-mb-30 tw-border-solid tw-p-15 tw-outline-none tw-bg-transparent tw-text-16 tw-text-white tw-font-sans tw-w-full" +
          " tw-uppercase" + (hasError ? " tw-border-red tw-text-red" : "")}
        type="text"
        placeholder={hasError ? "Tournament name is required" : "My tournament name..."}
        value={tournamentName} onChange={(e) => {
        setTournamentName(e.target.value)
        setHasError(false)
      }}/>
      <button
        className="tw-border-solid tw-border tw-border-green tw-bg-green/20 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 hover:tw-text-white/75 tw-font-sans tw-text-white tw-uppercase tw-w-full tw-text-16 tw-font-500 tw-mb-15 tw-p-12 tw-cursor-pointer disabled:tw-opacity-50"
        disabled={loading}
        onClick={() => {
          if (!tournamentName) {
            setHasError(true)
            return
          }
          setLoading(true)
          bracketApi.createTournament({
            bracketTemplateId: parseInt(props.templateId),
            title: tournamentName,
          })
            .then((res) => {
              window.location.href = props.tournamentsUrl
            })
            .catch((err) => {
              console.log(err)
              setLoading(false)
            })
        }}
      >Host
      </button>
      {cancelButton}
    </Modal>
  </>
}
