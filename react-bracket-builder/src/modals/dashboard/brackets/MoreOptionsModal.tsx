import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ReactComponent as EditIcon } from '../../../assets/icons/pencil.svg'
import { ReactComponent as PercentIcon } from '../../../assets/icons/percent.svg'
import { ReactComponent as ShareIcon } from '../../../assets/icons/share.svg'
import { ReactComponent as CopyIcon } from '../../../assets/icons/copy.svg'
import { ReactComponent as TrashIcon } from '../../../assets/icons/trash.svg'
import { ReactComponent as DollarIcon } from '../../../assets/icons/dollar_shield.svg'
import { ReactComponent as LockIcon } from '../../../assets/icons/lock.svg'
import { BracketData } from './BracketData'
import { TournamentModalVisibility } from './TournamentModalVisibility'

const BracketOptionButton = (props: {
  IconComponent: React.FunctionComponent
  label: string
  onClick: () => void
}) => {
  return (
    <button
      className="tw-flex tw-gap-10 tw-items-center tw-bg-transparent tw-text-white tw-uppercase tw-font-sans tw-border-none tw-cursor-pointer tw-p-0"
      onClick={props.onClick}
    >
      <div className="tw-h-24 tw-w-24">
        <props.IconComponent />
      </div>
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </button>
  )
}

const BracketOptionLink = (props: {
  IconComponent: React.FunctionComponent
  label: string
  url: string
}) => {
  return (
    <a
      href={props.url}
      className="tw-flex tw-gap-10 tw-items-center tw-text-white tw-uppercase tw-font-sans tw-cursor-pointer"
    >
      <props.IconComponent />
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </a>
  )
}

export const MoreOptionsModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  showModal: (modalName: keyof TournamentModalVisibility) => void
  bracketData: BracketData
  setBracketData: (data: BracketData) => void
}) => {
  addClickHandlers({
    buttonClassName: 'wpbb-more-options-button',
    onButtonClick: (b) => {
      props.setBracketData({
        id: parseInt(b.dataset.bracketId),
        title: b.dataset.bracketTitle,
        month: b.dataset.bracketMonth,
        year: b.dataset.bracketYear,
        fee: parseInt(b.dataset.fee),
        playBracketUrl: b.dataset.playBracketUrl,
        copyBracketUrl: b.dataset.copyBracketUrl,
        mostPopularPicksUrl: b.dataset.mostPopularPicksUrl,
      })
      props.setShow(true)
    },
  })
  return (
    <Modal show={props.show} setShow={props.setShow}>
      <div className="tw-flex tw-flex-col tw-gap-15">
        <BracketOptionLink
          IconComponent={PercentIcon}
          label="Most Popular Picks"
          url={props.bracketData.mostPopularPicksUrl}
        />
        <BracketOptionButton
          IconComponent={EditIcon}
          label="Edit Info"
          onClick={() => {
            props.showModal('editBracket')
          }}
        />
        <BracketOptionButton
          IconComponent={DollarIcon}
          label="Set Fee"
          onClick={() => {
            props.showModal('setTournamentFee')
          }}
        />
        <BracketOptionButton
          IconComponent={ShareIcon}
          label="Share"
          onClick={() => {
            props.showModal('shareBracket')
          }}
        />
        <BracketOptionLink
          IconComponent={CopyIcon}
          label="Duplicate"
          url={props.bracketData.copyBracketUrl}
        />
        <BracketOptionButton
          IconComponent={LockIcon}
          label="Lock"
          onClick={() => {
            props.showModal('lockLiveTournament')
          }}
        />
        <BracketOptionButton
          IconComponent={TrashIcon}
          label="Delete"
          onClick={() => {
            props.showModal('deleteBracket')
          }}
        />
      </div>
    </Modal>
  )
}
