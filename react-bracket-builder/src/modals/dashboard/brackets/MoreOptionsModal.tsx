import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ReactComponent as EditIcon } from '../../../assets/icons/pencil.svg'

const MoreOptionsButton = (props: {
  IconComponent: React.FunctionComponent
  label: string
  onClick: () => void
}) => {
  return (
    <button
      className="tw-flex tw-gap-10 tw-items-center tw-bg-transparent tw-text-white tw-uppercase tw-font-sans tw-border-none tw-cursor-pointer"
      onClick={props.onClick}
    >
      <props.IconComponent />
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </button>
  )
}

const MoreOptionsLink = (props: {
  IconComponent: React.FunctionComponent
  label: string
  url: string
}) => {
  return (
    <a
      href={props.url}
      className="tw-flex tw-gap-10 tw-items-center tw-bg-transparent tw-text-white tw-uppercase tw-font-sans tw-border-none tw-cursor-pointer"
    >
      <props.IconComponent />
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </a>
  )
}

export const MoreOptionsModal = (props: {
  setShowEditBracketModal: (show: boolean) => void
  setShowShareBracketModal: (show: boolean) => void
  setShowDeleteBracketModal: (show: boolean) => void
  setShowSetTournamentFeeModal: (show: boolean) => void
  setShowLockLiveTournamentModal: (show: boolean) => void
  show: boolean
  setShow: (show: boolean) => void
  bracketId: number | null
  setBracketId: (id: number) => void
  bracketTitle: string
  setBracketTitle: (title: string) => void
  bracketMonth: string
  setBracketMonth: (month: string) => void
  bracketYear: string
  setBracketYear: (year: string) => void
  bracketFee: number
  setBracketFee: (fee: number) => void
  playBracketUrl: string
  setPlayBracketUrl: (url: string) => void
  copyBracketUrl: string
  setCopyBracketUrl: (url: string) => void
  mostPopularPicksUrl: string
  setMostPopularPicksUrl: (url: string) => void
}) => {
  addClickHandlers({
    buttonClassName: 'wpbb-more-options-button',
    onButtonClick: (b) => {
      b.dataset.bracketId && props.setBracketId(parseInt(b.dataset.bracketId))
      b.dataset.bracketTitle && props.setBracketTitle(b.dataset.bracketTitle)
      b.dataset.bracketMonth && props.setBracketMonth(b.dataset.bracketMonth)
      b.dataset.bracketYear && props.setBracketYear(b.dataset.bracketYear)
      b.dataset.fee && props.setBracketFee(parseInt(b.dataset.fee))
      b.dataset.playBracketUrl &&
        props.setPlayBracketUrl(b.dataset.playBracketUrl)
      b.dataset.copyBracketUrl &&
        props.setCopyBracketUrl(b.dataset.copyBracketUrl)
      b.dataset.mostPopularPicksUrl &&
        props.setMostPopularPicksUrl(b.dataset.mostPopularPicksUrl)
      props.setShow(true)
    },
  })
  return (
    <Modal show={props.show} setShow={props.setShow}>
      <div className="tw-flex tw-flex-col tw-gap-15">
        <MoreOptionsButton
          IconComponent={EditIcon}
          label="Edit Info"
          onClick={() => {
            props.setShow(false)
            props.setShowEditBracketModal(true)
          }}
        />
      </div>
    </Modal>
  )
}
