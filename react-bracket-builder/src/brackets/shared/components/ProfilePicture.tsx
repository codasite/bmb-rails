import React from 'react'
import { ReactComponent as UserIcon } from '../assets/user.svg'

export const ProfilePicture = ({
  src,
  alt,
  color,
  shadow,
}: {
  src: string
  alt: string
  color?: 'red' | 'blue'
  shadow?: boolean
}) => {
  const backgroundVariants = {
    red: 'tw-bg-red/15',
    blue: 'tw-bg-blue/15',
  }
  const borderColorVariants = {
    red: 'tw-border-red',
    blue: 'tw-border-blue',
  }
  let className = `tw-h-52 tw-w-52 tw-flex tw-rounded-full tw-border-solid tw-border-1 ${borderColorVariants[color]} tw-justify-center tw-items-center ${backgroundVariants[color]}`
  const shadowVariants = {
    red: 'tw-shadow-red',
    blue: 'tw-shadow-blue',
  }
  className = shadow
    ? className + ` tw-shadow-lg ${shadowVariants[color]}`
    : className
  const colorVariants = {
    red: 'tw-text-red',
    blue: 'tw-text-blue',
  }
  const colorClass = colorVariants[color]
  if (src) {
    return (
      <img
        className={
          shadow
            ? `tw-h-52 tw-w-52 tw-rounded-full tw-shadow-lg ${shadowVariants[color]}`
            : 'tw-h-52 tw-w-52 tw-rounded-full'
        }
        src={src}
        alt={alt}
      />
    )
  }
  return (
    <div className={className}>
      <UserIcon className={colorClass} />
    </div>
  )
}
